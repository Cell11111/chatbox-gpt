<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Requirement;
use App\Models\RequirementVersion;
use App\Models\ImpactAnalysis;
use App\Models\Conflict;
use App\Models\ProjectFeature;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use App\Models\PendingUpdate;
use Illuminate\Support\Facades\Auth;
use App\Models\PendingResolution;
class ChatbotController extends Controller
{
    public function analyzeRequirements(Request $request)
    {
        // requirement text is required; requirement_id is optional
        $request->validate([
            'requirement' => 'required|string',
            'requirement_id' => 'nullable|integer|exists:requirements,id',
        ]);

        $requirementText = $request->input('requirement');
        $requirementId = $request->input('requirement_id', null);

        // -------------- ANALYSIS: handle splitting --------------
        $skipSplit = $request->boolean('skip_split', false);

        if ($requirementId && $skipSplit) {
            // Explicitly skip splitting when requested (e.g., analyzing existing requirement)
            $atomicRequirements = [$requirementText];
        } else {
            // Default: allow splitting into atomic requirements
            $splitResult = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a software requirement analyst. 
Your task is to split the given system requirement text into atomic requirements — only when there are multiple distinct requirements.
Return JSON array of strings.'
                    ],
                    ['role' => 'user', 'content' => $requirementText]
                ],
                'max_tokens' => 500
            ]);

            $responseContent = $splitResult->choices[0]->message->content ?? '[]';

            try {
                $atomicRequirements = json_decode(trim($responseContent), true);
                if (!is_array($atomicRequirements)) {
                    // fallback: treat entire input as single atomic requirement
                    $atomicRequirements = [$requirementText];
                }
            } catch (\Exception $e) {
                $atomicRequirements = [$requirementText];
            }
        }

        $allDefects = [];

        // For each atomic requirement, run defect detection (no DB writes here in preview)
        foreach ($atomicRequirements as $i => $atomicRequirement) {
            $analysisResult = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a requirement analyst. Detect and categorize the MOST CRITICAL defect in this system requirement. 

Analyze for the following defect types (in priority order):
- Ambiguity: Unclear, vague, or multiple interpretations possible
- Incompleteness: Missing essential information or details
- Inconsistency: Contradicts other parts or contains conflicting information
- Incorrectness: Factually wrong or technically incorrect
- Unmeasurability: No clear way to verify or measure success
- Unfeasibility: Technically impossible or impractical to implement
- Redundancy: Duplicates information already stated elsewhere
- Over-specification: Includes unnecessary implementation details
- Poor Organization: Poorly structured or difficult to understand

Return ONLY ONE defect - the most critical one found. Return it in JSON format: {"type":"Ambiguity","description":"...","suggestion":"..."}
If no defects are found, return: {"type":"None","description":"No defects found."}'
                    ],
                    ['role' => 'user', 'content' => $atomicRequirement]
                ],
                'max_tokens' => 500
            ]);

            $responseText = trim($analysisResult->choices[0]->message->content ?? '{}');

            try {
                $defect = json_decode($responseText, true);
                // Ensure we have a single defect object
                if (!is_array($defect) || !isset($defect['type'])) {
                    $defect = ['type' => 'None', 'description' => 'No defects found.'];
                }
                // Wrap in array for consistency with existing code
                $defects = [$defect];
            } catch (\Exception $e) {
                $defects = [['type' => 'None', 'description' => 'No defects found.']];
            }

            $allDefects[] = [
                'atomic_requirement' => $atomicRequirement,
                'defects' => $defects
            ];
        }

        // If requirement_id is provided -> follow your old behavior (save atomic reqs, versions, defects)
        if ($requirementId) {
            // load original and replicate old logic (delete original, create new ones, store defects)
            $original = Requirement::findOrFail($requirementId);

            $title = $original->title;
            $category = $original->category;
            $priority = $original->priority;
            $projectId = $original->project_id;
            $featureId = $original->feature_id;

            // delete original
            $original->delete();
            RequirementVersion::where('requirement_id', $requirementId)->delete();

            $savedResults = [];
            foreach ($allDefects as $i => $entry) {
                $atomicTitle = (count($allDefects) > 1) ? $title . ' (Part ' . ($i + 1) . ')' : $title;

                // create new requirement and version
                $newRequirement = Requirement::create([
                    'title' => $atomicTitle,
                    'description' => $entry['atomic_requirement'],
                    'category' => $category,
                    'priority' => $priority,
                    'project_id' => $projectId,
                    'feature_id' => $featureId,
                    'user_id' => Auth::id(),
                    'status' => 'Approved',
                ]);

                RequirementVersion::create([
                    'requirement_id' => $newRequirement->id,
                    'project_id' => $projectId,
                    'title' => $atomicTitle,
                    'description' => $entry['atomic_requirement'],
                    'category' => $category,
                    'priority' => $priority,
                    'status' => 'Approved',
                    'version' => 1,
                    'created_at' => now(),
                    'feature_id' => $featureId,
                    'author_id' => Auth::id(),
                ]);

                // store only the first defect into DB (skip 'None')
                if (!(count($entry['defects']) === 1 && $entry['defects'][0]['type'] === 'None')) {
                    // Store only the first defect
                    $def = $entry['defects'][0];
                    Defect::create([
                        'requirement_id' => $newRequirement->id,
                        'description' => $def['description'] ?? null,
                        'type' => $def['type'] ?? 'Unknown',
                        'suggestion' => $def['suggestion'] ?? null,
                    ]);
                }

                $savedResults[] = [
                    'requirement_id' => $newRequirement->id,
                    'defects' => $entry['defects']
                ];
            }

            return response()->json([
                'atomic_requirements' => array_column($allDefects, 'atomic_requirement'),
                'results' => $savedResults
            ]);
        }

        // Otherwise (preview mode): return the analysis without touching DB
        return response()->json([
            'atomic_requirements' => array_column($allDefects, 'atomic_requirement'),
            'results' => array_map(function ($e) {
                return ['defects' => $e['defects']];
            }, $allDefects),
        ]);
    }

    public function autoFix(Request $request)
    {
        $request->validate([
            'requirement_id' => 'required|exists:requirements,id',
            'suggestion' => 'required|string',
            'field' => 'required|in:title,description',
            'defect_id' => 'required|exists:defects,id',
        ]);


        $suggestion = $request->input('suggestion');
        $field = $request->input('field');
        $requirement = Requirement::findOrFail($request->requirement_id);
        $defect = Defect::findOrFail($request->defect_id);

        if (auth()->user()->role === 'software engineer') {
            $previousData = $requirement->only($request->field, 'status');
            // Prompt AI to rewrite the requirement based on the suggestion
            $aiPrompt = "Here is the original $field of a requirement: '{$requirement->$field}'.\n" .
                "Here is a suggestion to improve it: '{$suggestion}'.\n" .
                "Please rewrite the $field to incorporate the suggestion in a way that improves clarity, precision, and alignment with the intent of the original text.\n" .
                "Provide only the rewritten $field, with no quotes, no prefixes, and no explanations. Just the improved text.";

            $result = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a requirement improvement assistant.'],
                    ['role' => 'user', 'content' => $aiPrompt],
                ],
                'max_tokens' => 300,
            ]);

            $newValue = trim($result->choices[0]->message->content);

            PendingUpdate::create([
                'requirement_id' => $requirement->id,
                'project_id' => $requirement->project_id,
                'updated_data' => json_encode([$request->field => $newValue]),
                'previous_data' => json_encode($previousData),
                'submitted_by' => auth()->id(),
                'status' => 'Pending',
            ]);

            // Mark requirement as Pending while awaiting review
            $requirement->update(['status' => 'Pending']);

            $defect->delete();

            return response()->json([
                'pending' => true,
                'message' => 'Auto-fix submitted for approval.'
            ]);
        }

        $aiPrompt = "Here is the original $field of a requirement: '{$requirement->$field}'.\n" .
            "Here is a suggestion to improve it: '{$suggestion}'.\n" .
            "Please rewrite the $field to incorporate the suggestion in a way that improves clarity, precision, and alignment with the intent of the original text.\n" .
            "Provide only the rewritten $field, with no quotes, no prefixes, and no explanations. Just the improved text.";

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a requirement improvement assistant.'],
                ['role' => 'user', 'content' => $aiPrompt],
            ],
            'max_tokens' => 300,
        ]);

        $newValue = trim($result->choices[0]->message->content);
        
        // Update the requirement first
        $requirement->$field = $newValue;
        $requirement->save();
        
        // Then create version with updated values
        $latestVersion = $requirement->versions()->max('version') ?? 0;
        RequirementVersion::create([
            'requirement_id' => $requirement->id,
            'project_id' => $requirement->project_id,
            'version' => $latestVersion + 1,
            'title' => $requirement->title,
            'description' => $requirement->description,
            'category' => $requirement->category,
            'priority' => $requirement->priority,
            'status' => $requirement->status,
            'feature_id' => $requirement->feature_id,
            'author_id' => Auth::id(),
        ]);

        // Delete the defect after fixing
        $defect->delete();

        return response()->json([
            'updated' => true,
            'new_value' => $newValue,
            'defect_deleted' => true
        ]);
    }

    public function analyzeImpact(Request $request)
    {
        $request->validate([
            'changes' => 'required|array',
            'changes.*.requirement_id' => 'required|integer|exists:requirements,id',
            'changes.*.field' => 'required|string',
            'changes.*.old' => 'required|string',
            'changes.*.new' => 'required|string',
        ]);

        $changes = $request->input('changes');
        $impactResults = [];

        foreach ($changes as $change) {
            // Fetch related requirement context
            $requirement = Requirement::findOrFail($change['requirement_id']);
            $requirementNumber = $this->getRequirementNumber($requirement);

            // Check if this exact change has already been analyzed
            $existing = ImpactAnalysis::where('requirement_id', $change['requirement_id'])
                ->where('field', $change['field'])
                ->where('old', $change['old'])
                ->where('new', $change['new'])
                ->first();

            if ($existing) {
                $impact = json_decode($existing->impact, true) ?? [];
                $requirementImpact = $impact['requirement'] ?? 'No specific impact provided by the analysis.';
                $otherRequirementsImpact = $impact['other_requirements'] ?? 'No other requirements appear to be impacted.';

                // Map IDs to numbering in both "other requirements" impact text and recommendation
                $idNumberMap = $this->buildIdToNumberMap($requirement->project_id);
                $otherRequirementsImpact = $this->replaceIdsWithNumbers($otherRequirementsImpact, $idNumberMap);
                $recommendation = $existing->recommendation ?: 'No recommendation available.';
                $recommendation = $this->replaceIdsWithNumbers($recommendation, $idNumberMap);

                $impactResults[] = [
                    'requirement_id' => $change['requirement_id'],
                    'requirement_number' => $requirementNumber,
                    'description' => $requirement->description,
                    'project_title' => $requirement->project->name,
                    'field' => $change['field'],
                    'old' => $change['old'],
                    'new' => $change['new'],
                    'impact' => [
                        'requirement' => nl2br(e($requirementImpact)),
                        'other_requirements' => nl2br(e($otherRequirementsImpact)),
                    ],
                    'recommendation' => nl2br(e($recommendation)),
                ];
                continue;
            }

            // Build context from requirement
            $mainContext = "Requirement Context:\n" .
                "- Title: {$requirement->title}\n" .
                "- Description: {$requirement->description}\n" .
                "- Category: {$requirement->category}\n" .
                "- Priority: {$requirement->priority}\n" .
                "- Status: {$requirement->status}\n";

            // Only get requirements from same project, exclude archived
            $otherRequirements = Requirement::where('id', '!=', $requirement->id)
                ->where('project_id', $requirement->project_id)
                ->where('status', '!=', 'Rejected')
                ->limit(50)
                ->get();

            $othersContext = '';
            foreach ($otherRequirements as $r) {
                $othersContext .= "- [#{$r->id}] {$r->title}: {$r->description} (Category: {$r->category}, Priority: {$r->priority}, Status: {$r->status})\n";
            }

            $prompt = <<<EOT
$mainContext

CHANGE DETAILS:
- Field Changed: {$change['field']}
- From: "{$change['old']}"
- To: "{$change['new']}"

OTHER REQUIREMENTS:
$othersContext

Analyze the impact of this change **based only on the provided context**.
1. Impact on the requirement itself: Provide 2-3 bullet points.
2. Impact on other requirements:
   - For each impacted requirement, include its ID, description, and the specific impact.
     Example format:
     Requirement #2 – Description: "Redesign the user interface."
     Impact: This change may alter the layout dependencies or UI flow.
   - Do not list or mention any requirement that is not impacted.
3. Recommendation: "Approve" or "Reject" this change with an explanation.
EOT;

            // Call OpenAI to analyze
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a requirements impact analysis assistant. Use only the provided data.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 800,
            ]);

            $impactAnalysis = trim($result->choices[0]->message->content ?? '');

            preg_match('/Impact on the requirement itself:(.*?)(?=Impact on other requirements:|$)/is', $impactAnalysis, $requirementImpactMatch);
            preg_match('/Impact on other requirements:(.*?)(?=Recommendation:|$)/is', $impactAnalysis, $otherRequirementsImpactMatch);
            preg_match('/Recommendation:\s*(.*)/is', $impactAnalysis, $recommendationMatch);

            $requirementImpact = $this->cleanText(trim($requirementImpactMatch[1] ?? 'No specific impact provided by the analysis.'));
            $otherRequirementsImpact = $this->cleanText(trim($otherRequirementsImpactMatch[1] ?? 'No other requirements appear to be impacted.'));

            // Map IDs to numbering in both "other requirements" impact text and recommendation
            $idNumberMap = $this->buildIdToNumberMap($requirement->project_id);
            $otherRequirementsImpact = $this->replaceIdsWithNumbers($otherRequirementsImpact, $idNumberMap);
            $recommendation = $this->cleanText(trim($recommendationMatch[1] ?? 'No recommendation provided.'));
            $recommendation = $this->replaceIdsWithNumbers($recommendation, $idNumberMap);

            ImpactAnalysis::create([
                'requirement_id' => $change['requirement_id'],
                'field' => $change['field'],
                'old' => $change['old'],
                'new' => $change['new'],
                'impact' => json_encode([
                    'requirement' => $requirementImpact,
                    'other_requirements' => $otherRequirementsImpact,
                ]),
                'recommendation' => $recommendation,
            ]);

            $impactResults[] = [
                'requirement_id' => $change['requirement_id'],
                'requirement_number' => $requirementNumber,
                'description' => $requirement->description,
                'project_title' => $requirement->project->name,
                'field' => $change['field'],
                'old' => $change['old'],
                'new' => $change['new'],
                'impact' => [
                    'requirement' => nl2br(e($requirementImpact)),
                    'other_requirements' => nl2br(e($otherRequirementsImpact)),
                ],
                'recommendation' => nl2br(e($recommendation)) ?: 'No recommendation available.',
            ];
        }

        return response()->json([
            'impact_analysis' => collect($impactResults)
                ->sortBy('requirement_id') // Ensure consistent ordering
                ->values()
                ->toArray(),
        ]);
    }

    private function cleanText($text)
    {
        return preg_replace([
            '/^\d+\.\s*/m',            // Remove numbering
            '/^\s*-\s*/m',             // Fix bullet points
        ], '', trim($text));
    }

    private function buildIdToNumberMap(int $projectId): array
    {
        $map = [];
        try {
            $projects = Project::orderBy('id')->pluck('id')->values();
            $pIdx = $projects->search($projectId);
            if ($pIdx === false) return $map;
            $projectNumber = $pIdx + 1;

            $features = ProjectFeature::where('project_id', $projectId)->orderBy('id')->get();
            foreach ($features as $fi => $feature) {
                $featureIndex = $fi + 1;
                $reqs = Requirement::where('project_id', $projectId)
                    ->where('feature_id', $feature->id)
                    ->orderBy('id')
                    ->get();
                foreach ($reqs as $ri => $req) {
                    $reqIndex = $ri + 1;
                    $map[$req->id] = $projectNumber . '.' . $featureIndex . '.' . $reqIndex;
                }
            }
        } catch (\Exception $e) {
            // ignore and return whatever built
        }
        return $map;
    }

    private function replaceIdsWithNumbers(string $text, array $idNumberMap): string
    {
        if (!$text || empty($idNumberMap)) return $text;
        // Replace patterns like "[#12]", "Requirement #12", or "ID 12"
        $patterns = [
            '/\[#(\d+)\]/',
            '/Requirement\s*#(\d+)/i',
            '/ID\s*(\d+)/i',
        ];
        foreach ($patterns as $pattern) {
            $text = preg_replace_callback($pattern, function ($m) use ($idNumberMap) {
                $id = (int)$m[1];
                $num = $idNumberMap[$id] ?? $m[1];
                // Preserve bracket/hash context if present
                if (str_starts_with($m[0], '[#')) {
                    return '[#' . $num . ']';
                }
                if (stripos($m[0], 'Requirement') !== false) {
                    return 'Requirement #' . $num;
                }
                if (stripos($m[0], 'ID') !== false) {
                    return 'ID ' . $num;
                }
                return (string)$num;
            }, $text);
        }
        return $text;
    }

    private function getRequirementNumber(Requirement $r)
    {
        try {
            $projectIds = Project::orderBy('id')->pluck('id')->values();
            $pIdx = $projectIds->search($r->project_id);
            if ($pIdx === false) return (string)$r->id;
            $projectNumber = $pIdx + 1;

            $featureIds = $r->project->features->sortBy('id')->pluck('id')->values();
            $fIdx = $featureIds->search($r->feature_id);
            if ($fIdx === false) return (string)$r->id;
            $featureIndex = $fIdx + 1;

            $reqIds = Requirement::where('project_id', $r->project_id)
                ->where('feature_id', $r->feature_id)
                ->orderBy('id')
                ->pluck('id')
                ->values();
            $rIdx = $reqIds->search($r->id);
            if ($rIdx === false) return (string)$r->id;
            $reqIndex = $rIdx + 1;

            return $projectNumber . '.' . $featureIndex . '.' . $reqIndex;
        } catch (\Exception $e) {
            return (string)$r->id;
        }
    }

    public function analyzeRequirementsForConflicts(Request $request)
    {
        // Scope projects based on user role: engineers only analyze their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $projects = Project::whereHas('engineers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->get();
        } else {
            $projects = Project::all(); // Managers/Admins analyze all projects
        }

        if ($projects->isEmpty()) {
            return response()->json(['message' => 'No projects found to analyze.'], 404);
        }

        try {
            foreach ($projects as $project) {
                $projectId = $project->id;
                // Modify the query to exclude archived requirements
                $requirements = Requirement::where('project_id', $projectId)
                    ->where('status', '!=', 'Rejected')  // Add this line to exclude archived requirements
                    ->get();

                if ($requirements->isEmpty()) {
                    Log::info("No active requirements found for project ID {$projectId}.");
                    continue;
                }

                // Build ID-to-number map for this project
                $numberMap = $this->buildIdToNumberMap($projectId);

                // Build the formatted list of requirements for the current project using numbering
                $requirementList = '';
                foreach ($requirements as $req) {
                    $reqNumber = $numberMap[$req->id] ?? $req->id;
                    $requirementList .= "Requirement #{$reqNumber}: {$req->description}\n";
                }

                // Define the prompt for GPT
                $prompt = <<<EOT
You are a requirements analyst. Below is a list of system requirements. Each requirement is labeled with a number (e.g., Requirement #1.1.1). Analyze them for potential conflicts between requirements, such as:

- Duplicated requirements (same meaning, different wording)
- Contradictory requirements (they cannot both be true)
- Overlapping requirements (they partially repeat each other)

For each conflict, suggest a resolution. Use the following resolution options:
1. Combine and Reword
2. Prioritize One Over the Other

Return the conflicts and resolutions as a JSON object. Use the requirement NUMBERS (not IDs) from the list below in your response:

[
  {
    "requirement_a_number": "1.1.1",
    "requirement_b_number": "1.1.3",
    "type": "Duplication",
    "description": "Both requirements describe the same login behavior.",
    "suggestion": "Combine and Reword"
  },
  ...
]

If no conflicts are found, return:
[
  {
    "type": "None",
    "description": "No conflicts between requirements."
  }
]

Requirements:
$requirementList
EOT;

                // Call OpenAI API
                $result = OpenAI::chat()->create([
                    'model' => 'gpt-4',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a requirements analyst.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 1000,
                ]);

                $responseText = trim($result->choices[0]->message->content ?? '');
                $conflicts = json_decode($responseText, true);

                if (!is_array($conflicts)) {
                    Log::error("Invalid JSON from OpenAI for project ID {$projectId}.");
                    continue;
                }

                // Build reverse map: number -> ID
                $reverseMap = array_flip($numberMap);

                foreach ($conflicts as $conflict) {
                    if (isset($conflict['type']) && $conflict['type'] === 'None') {
                        continue;
                    }

                    // Convert requirement numbers back to IDs
                    $reqANumber = $conflict['requirement_a_number'] ?? null;
                    $reqBNumber = $conflict['requirement_b_number'] ?? null;

                    if (!$reqANumber || !$reqBNumber || !isset($conflict['description'])) {
                        Log::error("Incomplete conflict entry for project ID {$projectId}: " . json_encode($conflict));
                        continue;
                    }

                    $reqAId = $reverseMap[$reqANumber] ?? null;
                    $reqBId = $reverseMap[$reqBNumber] ?? null;

                    if (!$reqAId || !$reqBId) {
                        Log::error("Could not map requirement numbers to IDs for project ID {$projectId}: {$reqANumber}, {$reqBNumber}");
                        continue;
                    }

                    $existingConflict = Conflict::where('requirement_a_id', $reqAId)
                        ->where('requirement_b_id', $reqBId)
                        ->where('project_id', $projectId)
                        ->first();

                    if ($existingConflict) {
                        Log::info("Conflict already exists: #{$reqANumber} and #{$reqBNumber} in project ID {$projectId}.");
                        continue;
                    }

                    Conflict::create([
                        'requirement_a_id' => $reqAId,
                        'requirement_b_id' => $reqBId,
                        'summary' => $conflict['description'],
                        'severity' => 'Medium',
                        'status' => 'Pending',
                        'project_id' => $projectId,
                        'resolution' => $conflict['suggestion'] ?? null,
                    ]);
                }
            }

            return response()->json(['message' => 'Conflict analysis completed for all projects. Check the conflicts table.']);

        } catch (\Exception $e) {
            Log::error("Conflict analysis failed: " . $e->getMessage());
            return response()->json(['message' => 'Conflict analysis failed.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate a suggested combined requirement for a conflict using AI.
     */
    public function suggestCombined(Request $request)
    {
        $request->validate([
            'conflict_id' => 'required|integer|exists:conflicts,id',
        ]);

        $conflict = Conflict::findOrFail($request->input('conflict_id'));

        $requirementA = Requirement::find($conflict->requirement_a_id);
        $requirementB = Requirement::find($conflict->requirement_b_id);

        if (!$requirementA || !$requirementB) {
            return response()->json(['message' => 'Requirements not found for this conflict.'], 404);
        }

        try {
            $aiPrompt = "You are a senior requirements engineer. Given two requirements, produce a concise, clear, single combined requirement that preserves intent from both.\n\nRequirement A Title: {$requirementA->title}\nRequirement A Description: {$requirementA->description}\n\nRequirement B Title: {$requirementB->title}\nRequirement B Description: {$requirementB->description}\n\nReturn ONLY the combined requirement description with no extra commentary.";

            $aiResult = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a requirements authoring assistant.'],
                    ['role' => 'user', 'content' => $aiPrompt],
                ],
                'max_tokens' => 400,
            ]);

            $suggestion = trim($aiResult->choices[0]->message->content ?? '');
            if ($suggestion === '') {
                $suggestion = null;
            }

            return response()->json(['suggestion' => $suggestion]);
        } catch (\Exception $e) {
            try {
                \Illuminate\Support\Facades\Log::error('suggestCombined failed: ' . $e->getMessage());
            } catch (\Exception $_) {
            }
            return response()->json(['message' => 'AI suggestion generation failed.'], 500);
        }
    }

    public function suggestFeatures(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|integer|exists:projects,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        // 1.  Compose the prompt
        $messages = [
            [
                'role' => 'system',
                'content' => <<<PROMPT
You are a senior software architect. 
Given a short project description, identify the MAIN FEATURE AREAS a requirements
team should cover.  
Always include at least these high-level areas if relevant: Functional,
Security, Quality (performance, reliability, scalability, usability), 
Integration, Regulatory.  
For each area list 3–6 concrete features/subtopics they should elicit requirements for.

Respond STRICTLY with valid JSON in the format:
[
  {
    "category": "Security",
    "features": [
      "User authentication",
      "Role-based access control",
      "Data encryption at rest and in transit"
    ]
  },
  ...
]
NO explanations, no preamble, only JSON.
PROMPT
            ],
            ['role' => 'user', 'content' => "Project: {$validated['name']}\n\n{$validated['description']}"],
        ];

        // 2.  Call OpenAI (same pattern you used before)
        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => $messages,
                'max_tokens' => 600,
                'temperature' => 0.3,
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error communicating with AI service.'], 500);
        }

        $json = trim($result->choices[0]->message->content);
        $areas = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error: ' . json_last_error_msg());
            return response()->json(['message' => 'Invalid JSON from AI response.'], 500);
        }

        // 3.  Persist (optional)
        try {
            foreach ($areas as $area) {
                foreach ($area['features'] as $feature) {
                    ProjectFeature::create([
                        'project_id' => $validated['project_id'],
                        'category' => $area['category'],
                        'feature' => $feature,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Database Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error saving features to the database.'], 500);
        }

        // 4.  Return to front-end
        return response()->json($areas);
    }

    public function applyResolution(Request $request)
    {
        $conflictId = $request->input('conflict_id');
        $priority = $request->input('priority');
        $combinedDescription = $request->input('combined_description');
        $combineInto = $request->input('combine_into');
        $comments = $request->input('comments');

        try {
            // Fetch the conflict
            $conflict = Conflict::findOrFail($conflictId);

            if ($conflict->status === 'Resolved') {
                return response()->json(['message' => 'Conflict is already resolved.'], 400);
            }

            // Get related requirements
            $requirementA = Requirement::find($conflict->requirement_a_id);
            $requirementB = Requirement::find($conflict->requirement_b_id);

            if (!$requirementA || !$requirementB) {
                return response()->json(['message' => 'One or both requirements are missing.'], 404);
            }

            // Determine the user's chosen resolution type
            $userChosenResolution = null;
            
            // If user provided combined_description, they chose "Combine and Reword"
            if (!empty($combinedDescription) && !empty($combineInto)) {
                $userChosenResolution = 'Combine and Reword';
            }
            // If user provided priority, they chose "Prioritize One Over the Other"
            elseif (!empty($priority)) {
                $userChosenResolution = 'Prioritize One Over the Other';
            }
            
            if (!$userChosenResolution) {
                return response()->json(['message' => 'No valid resolution method provided.'], 400);
            }

            // Process based on user's chosen resolution, not the AI's suggestion
            if ($userChosenResolution === 'Combine and Reword') {
                if (!$combineInto || !in_array($combineInto, ['requirement_a', 'requirement_b'])) {
                    return response()->json(['message' => 'Invalid choice for combining.'], 400);
                }

                // Generate an AI-suggested combined requirement (non-blocking)
                $aiSuggestion = null;
                try {
                    $aiPrompt = "You are a senior requirements engineer. Given two requirements, produce a concise, clear, single combined requirement that preserves intent from both.\n\nRequirement A Title: {$requirementA->title}\nRequirement A Description: {$requirementA->description}\n\nRequirement B Title: {$requirementB->title}\nRequirement B Description: {$requirementB->description}\n\nIf the user provided a proposed combined description, refine and improve it for clarity and precision. Return ONLY the improved combined requirement description with no extra commentary.";

                    $aiResult = OpenAI::chat()->create([
                        'model' => 'gpt-4',
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a requirements authoring assistant.'],
                            ['role' => 'user', 'content' => $aiPrompt],
                        ],
                        'max_tokens' => 500,
                    ]);

                    $aiSuggestion = trim($aiResult->choices[0]->message->content ?? '');
                    if ($aiSuggestion === '') {
                        $aiSuggestion = null;
                    }
                } catch (\Exception $e) {
                    // Log the AI error but do not fail the request
                    try {
                        \Illuminate\Support\Facades\Log::error('AI suggestion failed: ' . $e->getMessage());
                    } catch (\Exception $_) {
                        // ignore
                    }
                    $aiSuggestion = null;
                }

                // If user provided combined description use it, otherwise prefer AI suggestion
                $finalCombined = !empty($combinedDescription) ? $combinedDescription : ($aiSuggestion ?? '');

                // For software engineers: save as pending resolution (include AI suggestion if available)
                if (auth()->user()->role === 'software engineer') {
                    PendingResolution::create([
                        'conflict_id' => $conflictId,
                        'requirement_a_id' => $requirementA->id,
                        'requirement_b_id' => $requirementB->id,
                        'resolution' => $userChosenResolution,
                        'updated_data' => json_encode([
                            'description' => $finalCombined,
                            'combine_into' => $combineInto,
                            'ai_suggestion' => $aiSuggestion,
                        ]),
                        'proposed_by' => auth()->id(),
                        'comments' => $comments,
                    ]);

                    $response = ['message' => 'Resolution submitted for manager approval.'];
                    if ($aiSuggestion) {
                        $response['suggested_combined_description'] = $aiSuggestion;
                    }

                    return response()->json($response);
                }

                // For project managers: apply resolution directly (use AI suggestion if user left blank)
                if (auth()->user()->role === 'project manager') {
                    if ($combineInto === 'requirement_a') {
                        // Create version before updating
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementA->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementA->id,
                            'project_id' => $requirementA->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementA->title,
                            'description' => $finalCombined,
                            'category' => $requirementA->category,
                            'priority' => $requirementA->priority,
                            'status' => $requirementA->status,
                            'feature_id' => $requirementA->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementA->description = $finalCombined;
                        $requirementA->save();
                        
                        // Mark requirement B as archived and create version
                        $latestVersionB = RequirementVersion::where('requirement_id', $requirementB->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementB->id,
                            'project_id' => $requirementB->project_id,
                            'version' => $latestVersionB + 1,
                            'title' => $requirementB->title,
                            'description' => $requirementB->description,
                            'category' => $requirementB->category,
                            'priority' => $requirementB->priority,
                            'status' => 'Rejected',
                            'feature_id' => $requirementB->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementB->status = 'Rejected';
                        $requirementB->save();
                    } elseif ($combineInto === 'requirement_b') {
                        // Create version before updating
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementB->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementB->id,
                            'project_id' => $requirementB->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementB->title,
                            'description' => $finalCombined,
                            'category' => $requirementB->category,
                            'priority' => $requirementB->priority,
                            'status' => $requirementB->status,
                            'feature_id' => $requirementB->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementB->description = $finalCombined;
                        $requirementB->save();
                        
                        // Mark requirement A as archived and create version
                        $latestVersionA = RequirementVersion::where('requirement_id', $requirementA->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementA->id,
                            'project_id' => $requirementA->project_id,
                            'version' => $latestVersionA + 1,
                            'title' => $requirementA->title,
                            'description' => $requirementA->description,
                            'category' => $requirementA->category,
                            'priority' => $requirementA->priority,
                            'status' => 'Rejected',
                            'feature_id' => $requirementA->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementA->status = 'Rejected';
                        $requirementA->save();
                    }

                    // Update conflict status
                    $conflict->update([
                        'status' => 'Resolved',
                        'resolution_date' => now(),
                        'resolved_by' => auth()->id()
                    ]);

                    $response = ['message' => 'Resolution applied successfully.'];
                    if ($aiSuggestion) {
                        $response['suggested_combined_description'] = $aiSuggestion;
                    }

                    return response()->json($response);
                }
            } elseif ($userChosenResolution === 'Prioritize One Over the Other') {
                if (!$priority || !in_array($priority, ['requirement_a', 'requirement_b'])) {
                    return response()->json(['message' => 'Invalid priority choice.'], 400);
                }

                // For software engineers: save as pending resolution
                if (auth()->user()->role === 'software engineer') {
                    // Create pending resolution
                    PendingResolution::create([
                        'conflict_id' => $conflictId,
                        'requirement_a_id' => $requirementA->id,
                        'requirement_b_id' => $requirementB->id,
                        'resolution' => $userChosenResolution,
                        'updated_data' => json_encode([
                            'delete_requirement_id' => $priority === 'requirement_a' ? $requirementB->id : $requirementA->id,
                            'delete_requirement_description' => $priority === 'requirement_a' ? $requirementB->description : $requirementA->description,
                            'status' => 'Resolved',
                            'resolution_date' => now(),
                            'resolved_by' => auth()->id()
                        ]),
                        'proposed_by' => auth()->id(),
                        'comments' => $comments,
                    ]);

                    return response()->json(['message' => 'Resolution submitted for manager approval.']);
                }

                // For project managers: apply resolution directly
                if (auth()->user()->role === 'project manager') {
                    if ($priority === 'requirement_a') {
                        // Mark requirement B as archived and create version
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementB->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementB->id,
                            'project_id' => $requirementB->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementB->title,
                            'description' => $requirementB->description,
                            'category' => $requirementB->category,
                            'priority' => $requirementB->priority,
                            'status' => 'Rejected',
                            'feature_id' => $requirementB->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementB->status = 'Rejected';
                        $requirementB->save();
                    } else {
                        // Mark requirement A as archived and create version
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementA->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementA->id,
                            'project_id' => $requirementA->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementA->title,
                            'description' => $requirementA->description,
                            'category' => $requirementA->category,
                            'priority' => $requirementA->priority,
                            'status' => 'Rejected',
                            'feature_id' => $requirementA->feature_id,
                            'author_id' => auth()->id(),
                        ]);
                        
                        $requirementA->status = 'Rejected';
                        $requirementA->save();
                    }

                    // Update conflict status
                    $conflict->update([
                        'status' => 'Resolved',
                        'resolution_date' => now(),
                        'resolved_by' => auth()->id()
                    ]);

                    return response()->json(['message' => 'Resolution applied successfully.']);
                }
            }

            return response()->json(['message' => 'Unauthorized user role.'], 403);
        } catch (\Exception $e) {
            Log::error("Resolution application failed: " . $e->getMessage());
            return response()->json(['message' => 'Resolution application failed.', 'error' => $e->getMessage()], 500);
        }
    }

}
