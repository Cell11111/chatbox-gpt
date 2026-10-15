<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requirement;
use App\Models\Defect;
use App\Models\ImpactAnalysis;
use App\Models\RequirementVersion;
use App\Models\Conflict;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\ProjectFeature;
use PHPUnit\Metadata\Api\Requirements;
use App\Models\PendingUpdate;
use app\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectReportExport;
use App\Models\Feedback;
use App\Models\PendingResolution;

class RequirementController extends Controller
{
    public function saveRequirements(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        $feature = ProjectFeature::findOrFail($request->feature_id);

        // Save the requirement
        $requirement = Requirement::create([
            'title' => $feature->feature,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'project_id' => $request->project_id,
            'feature_id' => $request->feature_id,
            'user_id' => Auth::id(),
            'status' => 'Approved',
        ]);

        // Save the initial version (now also including project_id)
        RequirementVersion::create([
            'requirement_id' => $requirement->id,
            'title' => $requirement->title,
            'description' => $requirement->description,
            'category' => $requirement->category,
            'priority' => $requirement->priority,
            'status' => 'Approved',
            'version' => 1,
            'project_id' => $request->project_id,
            'feature_id' => $request->feature_id,
            'author_id' => Auth::id(), // <-- NEW
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Requirement submitted!',
            'requirement_id' => $requirement->id
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:requirements,id',
            'status' => 'required|in:Applicable,Not Applicable,Pending'
        ]);

        $requirement = Requirement::findOrFail($request->id);
        if ($requirement->status !== $request->status) {
            $requirement->status = $request->status;
            $requirement->save();

            $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)->max('version') ?? 0;

            RequirementVersion::create([
                'requirement_id' => $requirement->id,
                'project_id' => $requirement->project_id,
                'title' => $requirement->title,
                'description' => $requirement->description,
                'category' => $requirement->category,
                'priority' => $requirement->priority,
                'status' => $requirement->status,
                'version' => $latestVersion + 1,
                'created_at' => now(),
                'feature_id' => $requirement->feature_id,
            ]);

        }

        return response()->json(['message' => 'Status updated successfully.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'feature_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:High,Medium,Low',
            'status' => 'nullable|in:Pending,Approved,Rejected,Completed'
        ]);

        $requirement = Requirement::findOrFail($id);

        if (auth()->user()->role === 'software engineer') {
            $previousData = $requirement->only('feature_id', 'description', 'category', 'priority', 'status');

            // Allow engineers to change the requirement 'status' immediately without manager approval.
            $requested = $request->only('feature_id', 'description', 'category', 'priority', 'status');

            // If status is provided and different, apply it immediately and create a version.
            if (isset($requested['status']) && $requested['status'] !== $requirement->status) {
                $oldStatus = $requirement->status;
                $requirement->update(['status' => $requested['status']]);

                // Create a new version reflecting the status change
                $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)->max('version') ?? 0;
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
            }

            // Remove status from the pending update payload so managers don't re-apply status
            if (isset($requested['status'])) {
                unset($requested['status']);
            }

            // Only store fields that actually changed compared to the current requirement
            $fieldsToCheck = ['feature_id', 'description', 'category', 'priority'];
            $changed = [];
            foreach ($fieldsToCheck as $f) {
                if (array_key_exists($f, $requested)) {
                    $val = $requested[$f];
                    $current = $requirement->$f;
                    // Normalize null/empty vs current
                    if ($val !== null && $val !== '' && $val != $current) {
                        $changed[$f] = $val;
                    }
                }
            }

            if (!empty($changed)) {
                PendingUpdate::create([
                    'requirement_id' => $requirement->id,
                    'project_id' => $requirement->project_id,
                    'updated_data' => json_encode($changed),
                    'previous_data' => json_encode(array_intersect_key($previousData, $changed)),
                    'submitted_by' => auth()->id(),
                    'status' => 'Pending',
                ]);
            }

            return redirect()->back()->with('success', 'Changes submitted. Status changes (if any) were applied immediately.');
        }

        $updateData = $request->only('description', 'category', 'priority');

        // Only project managers can modify the 'status' field
        if (auth()->user()->role === 'project manager') {
            $updateData['status'] = $request->input('status');
        }

        $requirement->update($updateData);

        if ($requirement->wasChanged()) {
            $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)->max('version') ?? 0;

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
        }

        return redirect()->back()->with('success', 'Requirement updated successfully.');
    }

    public function showPendingUpdates()
    {
        // Fetch pending updates with relationships
        $pendingUpdates = PendingUpdate::with(['requirement', 'submittedBy'])
            ->where('status', 'Pending')
            ->get();

        // Fetch pending resolutions
        $pendingResolutions = PendingResolution::with(['requirementA', 'requirementB', 'proposedBy'])
            ->where('status', 'Pending')
            ->get();

        return view('review-changes', compact('pendingUpdates', 'pendingResolutions'));
    }

    /**
     * Show the authenticated user's submitted change requests and their status.
     * Engineers can use this to see whether their pending updates were approved or rejected.
     */
    public function showMyPendingUpdates()
    {
        $userId = auth()->id();

        $pendingUpdates = PendingUpdate::with(['requirement', 'submittedBy'])
            ->where('submitted_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Also include resolutions proposed by the user
        $pendingResolutions = PendingResolution::with(['requirementA', 'requirementB'])
            ->where('proposed_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('my-changes', compact('pendingUpdates', 'pendingResolutions'));
    }

    public function reviewChange(Request $request, $id)
    {
        $pendingUpdate = PendingUpdate::findOrFail($id);

        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $updatedData = json_decode($pendingUpdate->updated_data, true) ?? [];

            $requirement = Requirement::findOrFail($pendingUpdate->requirement_id);

            // Ensure we do not change the live requirement 'status' field as part of
            // approving a pending update; status is managed separately.
            if (isset($updatedData['status'])) {
                unset($updatedData['status']);
            }

            // Apply only the permitted fields from the pending update to the requirement
            if (!empty($updatedData)) {
                $requirement->update($updatedData);
            }

            // Versioning logic (record a new version reflecting the applied changes)
            $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)->max('version') ?? 0;
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
                'author_id' => $pendingUpdate->submitted_by,
            ]);

            $pendingUpdate->update(['status' => 'Approved', 'reviewed_by' => auth()->id()]);

            return redirect()->back()->with('success', 'Change approved and applied.');
        }

        // Handle rejection: mark the pending update as Rejected but do NOT alter the
        // live requirement's status. Engineers can see the rejection in "My Changes".
        $pendingUpdate->update(['status' => 'Rejected', 'reviewed_by' => auth()->id()]);
        return redirect()->back()->with('success', 'Change rejected.');
    }

    public function getCategories($projectId)
    {
        $project = Project::with('features')->findOrFail($projectId);

        // Get unique categories for the project's features
        $categories = $project->features->pluck('category')->unique()->values();

        return response()->json($categories);
    }

    public function destroy($id)
    {
        $requirement = Requirement::findOrFail($id);
        $requirement->delete();

        return redirect()->back()->with('success', 'Requirement deleted successfully.');
    }

    public function ignoreDefect(Request $request)
    {
        $request->validate([
            'defect_id' => 'required|exists:defects,id',
        ]);

        $defect = Defect::findOrFail($request->defect_id);
        $defect->delete();

        return response()->json(['deleted' => true]);
    }

    public function showChangeLog(Request $request)
    {
        // Default the filter to today's date so the change log shows today's changes first.
        // Allow 'all' to explicitly request no date filtering.
        $filterDate = $request->query('date', date('Y-m-d'));

        $requirementsQuery = Requirement::with([
            'versions' => function ($query) {
                $query->orderBy('version', 'asc'); // Fetch versions in ascending order
            }
        ]);

        // If user is a software engineer, only show requirements from their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $assignedProjectIds = Project::whereHas('engineers', function ($q) {
                $q->where('user_id', auth()->id());
            })->pluck('id');
            $requirementsQuery->whereIn('project_id', $assignedProjectIds);
        }

        $requirements = $requirementsQuery->get();

        $changes = [];
        $originals = [];

        foreach ($requirements as $requirement) {
            Log::info('Processing Requirement', ['id' => $requirement->id]);
            $versions = $requirement->versions;
            $fields = ['title', 'description', 'category', 'priority', 'status'];

            Log::info('Loaded Versions', $versions->map(function ($v) {
                return [
                    'id' => $v->id,
                    'version' => $v->version,
                    'title' => $v->title,
                    'created_at' => $v->created_at->toDateTimeString()
                ];
            })->toArray());

            if ($versions->isNotEmpty()) {
                $original = $versions->firstWhere('version', 1);
                if ($filterDate === 'all' || $original->created_at->toDateString() === $filterDate) {
                    $originals[] = [
                        'requirement_id' => $requirement->id,
                        'project_id' => $requirement->project_id,
                        'project_name' => $requirement->project->name ?? 'Unknown Project',
                        'title' => $original->title,
                        'description' => $original->description,
                        'category' => $original->category,
                        'priority' => $original->priority,
                        'status' => 'Pending',
                        'created_at' => $original->created_at->toDateTimeString(),
                        'author' => $requirement->author->name ?? 'System',
                    ];
                    Log::info('Original Version Recorded', ['requirement_id' => $requirement->id]);
                }
            }
            $versionCounter = 2;

            // Compare the first version with the requirement table
            if ($versions->count() > 0) {
                $firstVersion = $versions->first();
                foreach ($fields as $field) {
                    if ($requirement->$field !== $firstVersion->$field) {
                        if ($filterDate === 'all' || $firstVersion->created_at->toDateString() === $filterDate) {
                            Log::info('Difference Found in Requirement vs First Version', [
                                'field' => $field,
                                'requirement_value' => $requirement->$field,
                                'version_value' => $firstVersion->$field
                            ]);

                            $changes[] = [
                                'requirement_id' => $requirement->id,
                                'project_id' => $requirement->project_id,
                                'project_name' => $requirement->project->name ?? 'Unknown Project',
                                'field' => $field,
                                'old' => $firstVersion->$field, // From Requirement table
                                'new' => $requirement->$field, // From first version
                                'version' => $firstVersion->version,
                                'date' => $firstVersion->created_at->format('Y-m-d'),
                                'created_at' => $firstVersion->created_at->toDateTimeString(),
                                'author' => $original->author->name ?? 'System',
                            ];
                            $versionCounter++;
                        }
                    }
                }
            }

            // Compare subsequent versions
            if ($versions->count() > 1) {
                for ($i = 1; $i < $versions->count(); $i++) {
                    $current = $versions[$i];
                    $previous = $versions[$i - 1];

                    foreach ($fields as $field) {
                        if ($current->$field !== $previous->$field) {
                            if ($filterDate === 'all' || $current->created_at->toDateString() === $filterDate) {
                                $changes[] = [
                                    'requirement_id' => $requirement->id,
                                    'project_id' => $requirement->project_id,
                                    'project_name' => $requirement->project->name ?? 'Unknown Project',
                                    'field' => $field,
                                    'old' => $current->$field,
                                    'new' => $previous->$field,
                                    'author' => $previous->author->name ?? 'System',
                                    'version' => $current->version + 1,
                                    'created_at' => $previous->created_at->toDateTimeString(),
                                    // Include full requirement details from the previous version
                                    'title' => $previous->title,
                                    'description' => $previous->description,
                                    'category' => $previous->category,
                                    'priority' => $previous->priority,
                                    'status' => $previous->status,
                                ];
                                $versionCounter++;
                            }
                        }
                    }
                }
            }
        }
        // Sort by project_id first, then requirement_id, then version
        usort($changes, function ($a, $b) {
            return [$a['project_id'], $a['requirement_id'], $a['version']] <=> [$b['project_id'], $b['requirement_id'], $b['version']];
        });
        
        // Group originals and changes by project
        $originalsByProject = collect($originals)->groupBy('project_id');
        $changesByProject = collect($changes)->groupBy('project_id');

        // Projects represented in the change log
        $projectIds = array_unique(array_merge(
            array_column($originals, 'project_id'),
            array_column($changes, 'project_id')
        ));

        $projects = Project::with(['features.requirements'])->whereIn('id', $projectIds)->orderBy('id')->get();

        // Distinct version numbers available per project
        $projectVersions = [];
        foreach ($projectIds as $pid) {
            $projectVersions[$pid] = RequirementVersion::where('project_id', $pid)
                ->select('version')
                ->distinct()
                ->orderBy('version')
                ->pluck('version')
                ->toArray();
        }
        
        return view('change-log', compact(
            'changes',
            'originals',
            'filterDate',
            'originalsByProject',
            'changesByProject',
            'projects',
            'projectVersions'
        ));
    }

    public function projectVersionSnapshot(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'target_version' => 'required|integer|min:1',
        ]);

        $projectId = (int) $request->project_id;
        $targetVersion = (int) $request->target_version;

        $project = Project::with([
            'features.requirements.versions' => function ($query) use ($targetVersion) {
                // Grab the nearest version up to the target (inclusive) for each requirement
                $query->where('version', '<=', $targetVersion)
                    ->orderByDesc('version')
                    ->with('author');
            },
            'features.requirements.feature',
            'features.requirements.author',
        ])->findOrFail($projectId);

        // Build project index (1-based) for numbering consistency
        $projectNumber = $request->input('project_number');
        if ($projectNumber === null) {
            // Fallback: compute based only on this project to avoid off-by-one when other projects are absent in the view context
            $projectOrder = Project::whereIn('id', [$projectId])->orderBy('id')->pluck('id')->toArray();
            $projectIdx = array_search($projectId, $projectOrder, true);
            $projectNumber = $projectIdx !== false ? $projectIdx + 1 : 1;
        }

        // Build feature index map (1-based) within this project
        $featureIndexMap = [];
        $featureRequirements = [];
        $featurePosition = 0;
        foreach ($project->features->sortBy('id') as $feature) {
            $featurePosition++;
            $featureIndexMap[$feature->id] = $featurePosition;
            $featureRequirements[$feature->id] = ($feature->requirements ?? collect())->sortBy('id')->values();
        }

        $requirementsSnapshot = [];

        foreach ($featureRequirements as $featureId => $requirements) {
            foreach ($requirements as $index => $requirement) {
                // Use the closest version at/before the target, or fall back to current requirement data
                $version = $requirement->versions->first();
                $activeVersion = $version ?? $requirement; // fallback ensures all requirements are listed

                $numberParts = array_filter([
                    $projectNumber,
                    $featureIndexMap[$featureId] ?? null,
                    $index + 1,
                ]);

                $requirementsSnapshot[] = [
                    'requirement_id' => $requirement->id,
                    'number' => implode('.', $numberParts) ?: $requirement->id,
                    'feature' => $requirement->feature->feature ?? 'N/A',
                    'title' => $activeVersion->title ?? $requirement->title,
                    'description' => $activeVersion->description ?? $requirement->description,
                    'category' => $activeVersion->category ?? $requirement->category,
                    'priority' => $activeVersion->priority ?? $requirement->priority,
                    'status' => $activeVersion->status ?? $requirement->status,
                    'version' => $activeVersion->version ?? 1,
                    'author' => optional($activeVersion->author)->name ?? 'System',
                    'timestamp' => optional($activeVersion->created_at)->toDateTimeString(),
                ];
            }
        }

        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'version' => $targetVersion,
            'requirements' => $requirementsSnapshot,
        ]);
    }

    public function showConflicts()
    {
        // Show conflicts based on role: engineers see only their assigned projects' conflicts
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $projectIds = Project::whereHas('engineers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->pluck('id');

            $conflicts = Conflict::whereIn('project_id', $projectIds)->get();
        } else {
            // Managers/Admins see all conflicts
            $conflicts = Conflict::all();
        }

        // Pass the conflicts to the view
        return view('view-conflict', compact('conflicts'));
    }

    public function showDefects()
    {
        // Filter defects based on role: engineers see only defects from their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $projectIds = Project::whereHas('engineers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->pluck('id');

            // Get requirements that have defects and are in assigned projects
            $requirements = Requirement::has('defects')
                ->with(['defects', 'feature', 'project'])
                ->whereIn('project_id', $projectIds)
                ->get();
        } else {
            // Managers/Admins see all defects
            $requirements = Requirement::has('defects')
                ->with(['defects', 'feature', 'project'])
                ->get();
        }

        return view('review-defects', compact('requirements'));
    }

    public function showAddFeature()
    {
        // Filter projects based on role: engineers see only their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $projects = Project::whereHas('engineers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->select('id', 'name')->get();
        } else {
            // Managers/Admins see all projects
            $projects = Project::select('id', 'name')->get();
        }

        return view('add-feature', compact('projects'));
    }

    public function projectStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'engineers' => 'nullable|array',
            'engineers.*' => 'exists:users,id'
        ]);

        try {
            $project = Project::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
            ]);

            // Attach selected engineers to the project
            if (!empty($validated['engineers']) && is_array($validated['engineers'])) {
                $project->engineers()->sync($validated['engineers']);
                Log::info('Engineers synced for project', [
                    'project_id' => $project->id,
                    'engineers' => $validated['engineers']
                ]);
            }

            return response()->json(['project_id' => $project->id]);
        } catch (\Exception $e) {
            Log::error('Project creation failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function projectUpdate(Request $request, $id)
    {
        if (!(auth()->check() && auth()->user()->role === 'project manager')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'engineers' => 'nullable|array',
            'engineers.*' => 'exists:users,id'
        ]);

        try {
            $project = Project::findOrFail($id);
            $project->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
            ]);

            if (array_key_exists('engineers', $validated) && is_array($validated['engineers'])) {
                $project->engineers()->sync($validated['engineers']);
            }

            return response()->json(['success' => true, 'message' => 'Project updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Project update failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showProject()
    {
        $query = Project::with('features');

        // If user is a software engineer, only show their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $query->whereHas('engineers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $projects = $query->get();

        // Do not abort if empty; view will handle empty-state messaging for unassigned users

        // Build the projectFeatures array, grouped by category
        $projectFeatures = [];
        foreach ($projects as $project) {
            $projectFeatures[$project->id] = $project->features->groupBy('category')->map(function ($features) {
                return $features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'title' => $feature->feature,
                    ];
                })->toArray();
            })->toArray();
        }

        // Return the view with projects and projectFeatures
        return view('submit-requirements', [
            'projects' => $projects,
            'projectFeatures' => $projectFeatures,
        ]);
    }

    public function viewProjects()
    {
        $query = Project::with([
            'features.requirements.defects',     // Load defects linked to each requirement
            'features',                          // Load features under each project
            'features.requirements.author',      // Load the user who authored the requirement
            'engineers'                          // Load assigned engineers for edit
        ]);

        // If user is a software engineer, only show their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $query->whereHas('engineers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $projects = $query->get();

        $categories = $projects->flatMap(function ($project) {
            return $project->features->pluck('category');
        })->unique()->toArray();

        return view('view-project', [
            'projects' => $projects,
            'categories' => $categories,
        ]);
    }

    public function listEngineers()
    {
        if (!(auth()->check() && auth()->user()->role !== 'software engineer')) {
            abort(403, 'Unauthorized action.');
        }
        $engineers = \App\Models\User::where('role', 'software engineer')->select('id', 'name')->orderBy('name')->get();
        return response()->json($engineers);
    }

    public function featureStore(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category' => 'required|string|max:255',
            'feature' => 'required|string|max:1000',
        ]);

        // Validate that engineers can only add features to their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $isAssigned = Project::where('id', $validated['project_id'])
                ->whereHas('engineers', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this project.',
                ], 403);
            }
        }

        try {
            $feature = ProjectFeature::create([
                'project_id' => $validated['project_id'],
                'category' => $validated['category'],
                'feature' => $validated['feature'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature created successfully.',
                'feature' => $feature,
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing feature: ', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create feature.',
            ], 500);
        }
    }

    public function getProject($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($project);
    }

    public function dashboard()
    {
        // If user is a software engineer, only load their assigned projects
        $projectsQuery = Project::query();
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $projectsQuery->whereHas('engineers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }
        $projects = $projectsQuery->get();
        $projectIds = $projects->pluck('id');

        $projectDurations = $projects->map(function ($project) {
            $duration = now()->diffForHumans($project->created_at, ['parts' => 2]);

            // Count changes grouped by authors
            $authorChanges = RequirementVersion::where('project_id', $project->id)
                ->select('author_id', \DB::raw('count(*) as count'))
                ->groupBy('author_id')
                ->with('author:id,name') // Assuming 'author' relationship exists in RequirementVersion
                ->get()
                ->map(function ($item) {
                    return [
                        'authorName' => $item->author->name ?? 'Unknown',
                        'changeCount' => $item->count,
                    ];
                });

            $changeCount = $authorChanges->sum('changeCount'); // Subtract initial version

            return [
                'name' => $project->name,
                'duration' => $duration,
                'changeCount' => max($changeCount, 0),
                'authorChanges' => $authorChanges,
            ];
        });

        $allStatuses = ['Approved', 'Pending'];

        // Filter requirements by assigned projects for engineers
        $requirementsQuery = Requirement::query();
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $requirementsQuery->whereIn('project_id', $projectIds);
        }

        $statusCounts = $requirementsQuery->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($allStatuses as $status) {
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
        }

        ksort($statusCounts);

        // Filter recent updates by assigned projects for engineers
        $recentUpdatesQuery = RequirementVersion::query();
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $recentUpdatesQuery->whereIn('project_id', $projectIds);
        }

        // Filter conflicts by assigned projects for engineers
        $conflictsQuery = Conflict::query();
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $conflictsQuery->whereHas('requirementA', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        // Filter defects by assigned projects for engineers
        $defectsQuery = Defect::query();
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $defectsQuery->whereHas('requirement', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        return [
            'projectDurations' => $projectDurations,
            'statusCounts' => $statusCounts,
            'recentUpdates' => $recentUpdatesQuery->latest()->take(5)->get(),
            'totalProjects' => $projects->count(),
            'totalRequirements' => $requirementsQuery->count(),
            'totalConflicts' => $conflictsQuery->count(),
            'totalPendingReviews' => \App\Models\PendingUpdate::where('status', 'Pending')->count() + \App\Models\PendingResolution::where('status', 'Pending')->count(),
            'outstandingDefects' => $defectsQuery->count(),
        ];
    }
    public function list()
    {
        // Filter projects based on role: engineers see only their assigned projects
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            $userId = auth()->id();
            $projects = Project::whereHas('engineers', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->select('id', 'name')->get();
        } else {
            // Managers/Admins see all projects
            $projects = Project::all(['id', 'name']);
        }
        return response()->json($projects);
    }

    public function exportReport()
    {
        return Excel::download(new ProjectReportExport, 'Final_Report.xlsx');
    }

    public function storeFeedback(Request $request, Project $project)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        $project->feedback()->create([
            'content' => $request->feedback,
            'author_id' => auth()->id(), // Adjust if using a guest system
        ]);

        return redirect()->back()->with('success', 'Feedback submitted successfully.');
    }

    public function destroyFeedback(Feedback $feedback)
    {
        if (auth()->user()->role !== 'project manager') {
            abort(403, 'Unauthorized action.');
        }

        $feedback->delete();

        return redirect()->back()->with('success', 'Feedback deleted successfully.');
    }

    public function destroyFeature($id)
    {
        $feature = ProjectFeature::findOrFail($id);

        $feature->requirements()->delete();

        // Delete the feature
        $feature->delete();

        return redirect()->back()->with('success', 'Feature deleted successfully.');
    }

    public function destroyProject($id)
    {
        // Only allow project managers to delete projects
        if (!auth()->check() || auth()->user()->role !== 'project manager') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        try {
            $project = Project::findOrFail($id);
            
            // Delete will cascade due to foreign key constraints or you can manually delete related records
            $project->delete();
            
            return response()->json(['message' => 'Project deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Project deletion failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete project.'], 500);
        }
    }

    public function dismissConflict(Request $request)
    {
        $conflictId = $request->input('conflict_id');
        $conflict = Conflict::find($conflictId);

        if (!$conflict) {
            return response()->json(['message' => 'Conflict not found.'], 404);
        }

        $conflict->delete();

        return response()->json(['message' => 'Conflict dismissed successfully.']);
    }

    public function reviewResolution(Request $request, $id)
    {
        $pendingResolution = PendingResolution::findOrFail($id);
        $action = $request->input('action');
        $comments = $request->input('comments');

        try {
            if ($action === 'approve') {
                $updatedData = json_decode($pendingResolution->updated_data, true);

                // Apply resolution based on type
                if ($pendingResolution->resolution === 'Combine and Reword') {
                    $combineInto = $updatedData['combine_into'] ?? null;

                    if (!$combineInto || !in_array($combineInto, ['requirement_a', 'requirement_b'])) {
                        return redirect()->back()->with('error', 'Invalid combine_into value.');
                    }

                    $requirementA = Requirement::findOrFail($pendingResolution->requirement_a_id);
                    $requirementB = Requirement::findOrFail($pendingResolution->requirement_b_id);

                    if ($combineInto === 'requirement_a') {
                        // Create version before updating requirement A
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementA->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementA->id,
                            'project_id' => $requirementA->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementA->title,
                            'description' => $updatedData['description'],
                            'category' => $requirementA->category,
                            'priority' => $requirementA->priority,
                            'status' => $requirementA->status,
                            'feature_id' => $requirementA->feature_id,
                            'author_id' => $pendingResolution->proposed_by,
                        ]);
                        
                        // Update requirement A and archive requirement B
                        $requirementA->update(['description' => $updatedData['description']]);
                        
                        // Create version for archived requirement B
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
                            'author_id' => $pendingResolution->proposed_by,
                        ]);
                        
                        $requirementB->update(['status' => 'Rejected']);
                    } elseif ($combineInto === 'requirement_b') {
                        // Create version before updating requirement B
                        $latestVersion = RequirementVersion::where('requirement_id', $requirementB->id)->max('version') ?? 0;
                        RequirementVersion::create([
                            'requirement_id' => $requirementB->id,
                            'project_id' => $requirementB->project_id,
                            'version' => $latestVersion + 1,
                            'title' => $requirementB->title,
                            'description' => $updatedData['description'],
                            'category' => $requirementB->category,
                            'priority' => $requirementB->priority,
                            'status' => $requirementB->status,
                            'feature_id' => $requirementB->feature_id,
                            'author_id' => $pendingResolution->proposed_by,
                        ]);
                        
                        // Update requirement B and archive requirement A
                        $requirementB->update(['description' => $updatedData['description']]);
                        
                        // Create version for archived requirement A
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
                            'author_id' => $pendingResolution->proposed_by,
                        ]);
                        
                        $requirementA->update(['status' => 'Rejected']);
                    }
                } elseif ($pendingResolution->resolution === 'Prioritize One Over the Other') {
                    // Archive the prioritized requirement instead of deleting
                    if (isset($updatedData['delete_requirement_id'])) {
                        $requirementToArchive = Requirement::find($updatedData['delete_requirement_id']);
                        if ($requirementToArchive) {
                            // Create version before archiving
                            $latestVersion = RequirementVersion::where('requirement_id', $requirementToArchive->id)->max('version') ?? 0;
                            RequirementVersion::create([
                                'requirement_id' => $requirementToArchive->id,
                                'project_id' => $requirementToArchive->project_id,
                                'version' => $latestVersion + 1,
                                'title' => $requirementToArchive->title,
                                'description' => $requirementToArchive->description,
                                'category' => $requirementToArchive->category,
                                'priority' => $requirementToArchive->priority,
                                'status' => 'Rejected',
                                'feature_id' => $requirementToArchive->feature_id,
                                'author_id' => $pendingResolution->proposed_by,
                            ]);
                            
                            $requirementToArchive->update(['status' => 'Rejected']);
                        }
                    }
                } else {
                    return redirect()->back()->with('error', 'Unknown resolution type.');
                }

                // Update conflict status
                $conflict = Conflict::findOrFail($pendingResolution->conflict_id);
                $conflict->update([
                    'status' => 'Resolved',
                    'resolution_date' => now(),
                    'resolved_by' => auth()->id(),
                    'comments' => $comments
                ]);

                // Update and keep the pending resolution
                $pendingResolution->update([
                    'status' => 'Approved',
                    'comments' => $comments,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id()
                ]);

                return redirect()->back()->with('success', 'Resolution approved and applied.');
            } elseif ($action === 'reject') {
                // Update pending resolution status instead of deleting
                $pendingResolution->delete();
                return redirect()->back()->with('success', 'Resolution rejected.');
            }

            return redirect()->back()->with('error', 'Invalid action.');
        } catch (\Exception $e) {
            Log::error("Resolution review failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Resolution review failed.');
        }
    }

    public function rollbackVersion(Request $request)
    {
        $request->validate([
            'requirement_id' => 'required|exists:requirements,id',
            'target_version' => 'required|integer|min:1',
        ]);

        try {
            $requirement = Requirement::findOrFail($request->requirement_id);
            
            // Find the target version
            $targetVersion = RequirementVersion::where('requirement_id', $requirement->id)
                ->where('version', $request->target_version)
                ->first();

            if (!$targetVersion) {
                return response()->json(['message' => 'Target version not found.'], 404);
            }

            // Get the latest version number
            $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)
                ->max('version') ?? 0;

            // Create a new version with the data from the target version
            RequirementVersion::create([
                'requirement_id' => $requirement->id,
                'project_id' => $requirement->project_id,
                'version' => $latestVersion + 1,
                'title' => $targetVersion->title,
                'description' => $targetVersion->description,
                'category' => $targetVersion->category,
                'priority' => $targetVersion->priority,
                'status' => $targetVersion->status,
                'feature_id' => $targetVersion->feature_id,
                'author_id' => auth()->id(),
            ]);

            // Update the requirement with the target version's data
            $requirement->update([
                'title' => $targetVersion->title,
                'description' => $targetVersion->description,
                'category' => $targetVersion->category,
                'priority' => $targetVersion->priority,
                'status' => $targetVersion->status,
            ]);

            $newVersion = $latestVersion + 1;
            
            return response()->json([
                'success' => true,
                'message' => "Requirement successfully rolled back to Version {$request->target_version}. New version {$newVersion} created."
            ]);
        } catch (\Exception $e) {
            Log::error("Rollback failed: " . $e->getMessage());
            return response()->json(['message' => 'Rollback failed: ' . $e->getMessage()], 500);
        }
    }

    public function rollbackProject(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'target_version' => 'required|integer|min:1',
        ]);

        try {
            $project = Project::with('requirements')->findOrFail($request->project_id);
            $targetVersionNum = (int) $request->target_version;
            $rolled = 0;

            foreach ($project->requirements as $requirement) {
                $targetVersion = RequirementVersion::where('requirement_id', $requirement->id)
                    ->where('version', $targetVersionNum)
                    ->first();

                if (!$targetVersion) {
                    continue; // skip requirements without that version
                }

                $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)
                    ->max('version') ?? 0;

                RequirementVersion::create([
                    'requirement_id' => $requirement->id,
                    'project_id' => $requirement->project_id,
                    'version' => $latestVersion + 1,
                    'title' => $targetVersion->title,
                    'description' => $targetVersion->description,
                    'category' => $targetVersion->category,
                    'priority' => $targetVersion->priority,
                    'status' => $targetVersion->status,
                    'feature_id' => $targetVersion->feature_id,
                    'author_id' => auth()->id(),
                ]);

                $requirement->update([
                    'title' => $targetVersion->title,
                    'description' => $targetVersion->description,
                    'category' => $targetVersion->category,
                    'priority' => $targetVersion->priority,
                    'status' => $targetVersion->status,
                ]);

                $rolled++;
            }

            return response()->json([
                'success' => true,
                'message' => "Project rolled back to Version {$targetVersionNum} for {$rolled} requirements.",
            ]);
        } catch (\Exception $e) {
            Log::error("Project rollback failed: " . $e->getMessage());
            return response()->json(['message' => 'Project rollback failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save a complete project snapshot at any time
     */
    public function saveProjectSnapshot(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'snapshot_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $project = Project::with(['features.requirements', 'features.requirements.defects'])->findOrFail($request->project_id);
            
            // Prepare snapshot data - include all current requirements with their versions
            $snapshotData = [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'project_description' => $project->description,
                'requirements' => [],
                'features' => [],
            ];

            // Store all requirements data at the time of snapshot
            foreach ($project->features as $feature) {
                $featureData = [
                    'id' => $feature->id,
                    'feature' => $feature->feature,
                    'category' => $feature->category,
                    'requirements' => [],
                ];

                foreach ($feature->requirements as $requirement) {
                    $featureData['requirements'][] = [
                        'id' => $requirement->id,
                        'title' => $requirement->title,
                        'description' => $requirement->description,
                        'category' => $requirement->category,
                        'priority' => $requirement->priority,
                        'status' => $requirement->status,
                    ];

                    $snapshotData['requirements'][] = [
                        'id' => $requirement->id,
                        'title' => $requirement->title,
                        'description' => $requirement->description,
                        'category' => $requirement->category,
                        'priority' => $requirement->priority,
                        'status' => $requirement->status,
                        'feature_id' => $requirement->feature_id,
                    ];
                }

                $snapshotData['features'][] = $featureData;
            }

            // Save the snapshot
            $snapshot = \App\Models\ProjectSnapshot::create([
                'project_id' => $request->project_id,
                'snapshot_name' => $request->snapshot_name,
                'description' => $request->description,
                'snapshot_data' => $snapshotData,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project snapshot saved successfully!',
                'snapshot_id' => $snapshot->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Project snapshot save failed: " . $e->getMessage());
            return response()->json(['message' => 'Snapshot save failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all snapshots for a project
     */
    public function getProjectSnapshots($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $snapshots = \App\Models\ProjectSnapshot::where('project_id', $projectId)
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'snapshots' => $snapshots,
            ]);
        } catch (\Exception $e) {
            Log::error("Get snapshots failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve snapshots: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rollback entire project to a specific snapshot
     */
    public function rollbackToSnapshot(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'snapshot_id' => 'required|exists:project_snapshots,id',
        ]);

        try {
            $project = Project::findOrFail($request->project_id);
            $snapshot = \App\Models\ProjectSnapshot::findOrFail($request->snapshot_id);

            // Verify snapshot belongs to this project
            if ($snapshot->project_id !== $project->id) {
                return response()->json(['message' => 'Snapshot does not belong to this project.'], 403);
            }

            $snapshotData = $snapshot->snapshot_data;
            $rolled = 0;
            $restored = 0;
            $removed = 0;

            // Get all snapshot requirement IDs
            $snapshotRequirementIds = collect($snapshotData['requirements'])->pluck('id')->toArray();

            // Remove requirements that exist now but weren't in the snapshot
            $currentRequirements = Requirement::where('project_id', $request->project_id)->get();
            foreach ($currentRequirements as $currentReq) {
                if (!in_array($currentReq->id, $snapshotRequirementIds)) {
                    // This requirement didn't exist in the snapshot - remove it
                    $currentReq->delete();
                    $removed++;
                }
            }

            // Restore all requirements to their state in the snapshot
            foreach ($snapshotData['requirements'] as $reqData) {
                $requirement = Requirement::find($reqData['id']);
                
                if (!$requirement) {
                    // Requirement was deleted - restore it
                    $requirement = Requirement::create([
                        'id' => $reqData['id'],
                        'title' => $reqData['title'],
                        'description' => $reqData['description'],
                        'category' => $reqData['category'],
                        'priority' => $reqData['priority'],
                        'status' => $reqData['status'],
                        'project_id' => $request->project_id,
                        'feature_id' => $reqData['feature_id'],
                        'user_id' => auth()->id(),
                    ]);

                    // Create initial version for restored requirement
                    RequirementVersion::create([
                        'requirement_id' => $requirement->id,
                        'project_id' => $requirement->project_id,
                        'version' => 1,
                        'title' => $reqData['title'],
                        'description' => $reqData['description'],
                        'category' => $reqData['category'],
                        'priority' => $reqData['priority'],
                        'status' => $reqData['status'],
                        'feature_id' => $reqData['feature_id'],
                        'author_id' => auth()->id(),
                    ]);

                    $restored++;
                } else {
                    // Requirement exists - update it
                    // Create a new version before rolling back
                    $latestVersion = RequirementVersion::where('requirement_id', $requirement->id)
                        ->max('version') ?? 0;

                    RequirementVersion::create([
                        'requirement_id' => $requirement->id,
                        'project_id' => $requirement->project_id,
                        'version' => $latestVersion + 1,
                        'title' => $reqData['title'],
                        'description' => $reqData['description'],
                        'category' => $reqData['category'],
                        'priority' => $reqData['priority'],
                        'status' => $reqData['status'],
                        'feature_id' => $reqData['feature_id'],
                        'author_id' => auth()->id(),
                    ]);

                    // Update the requirement to match snapshot state
                    $requirement->update([
                        'title' => $reqData['title'],
                        'description' => $reqData['description'],
                        'category' => $reqData['category'],
                        'priority' => $reqData['priority'],
                        'status' => $reqData['status'],
                    ]);
                }

                $rolled++;
            }

            $message = "Project successfully rolled back to snapshot '{$snapshot->snapshot_name}'. {$rolled} requirements restored.";
            if ($restored > 0) {
                $message .= " ({$restored} deleted requirements brought back)";
            }
            if ($removed > 0) {
                $message .= " ({$removed} new requirements removed)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("Snapshot rollback failed: " . $e->getMessage());
            return response()->json(['message' => 'Snapshot rollback failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a snapshot
     */
    public function deleteSnapshot($snapshotId)
    {
        try {
            $snapshot = \App\Models\ProjectSnapshot::findOrFail($snapshotId);
            
            $snapshot->delete();

            return response()->json([
                'success' => true,
                'message' => 'Snapshot deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Delete snapshot failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete snapshot: ' . $e->getMessage()], 500);
        }
    }

}