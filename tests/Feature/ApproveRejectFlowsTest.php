<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\Requirement;
use App\Models\RequirementVersion;
use App\Models\PendingUpdate;
use App\Models\PendingResolution;
use App\Models\Conflict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ApproveRejectFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_update_approval_and_rejection()
    {
        $manager = User::factory()->create(['role' => 'project manager']);
        $engineer = User::factory()->create(['role' => 'software engineer']);

        $project = Project::create(['name' => 'Proj A', 'description' => 'A']);
        $feature = ProjectFeature::create(['project_id' => $project->id, 'category' => 'Core', 'feature' => 'F1']);

        $requirement = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => $feature->feature,
            'description' => 'Original',
            'category' => 'Core',
            'priority' => 'Low',
            'status' => 'Approved',
            'author_id' => $engineer->id,
        ]);

        // Create a pending update (engineer submitted)
        $pending = PendingUpdate::create([
            'requirement_id' => $requirement->id,
            'project_id' => $project->id,
            'updated_data' => json_encode(['description' => 'Updated description', 'priority' => 'High']),
            'previous_data' => json_encode(['description' => 'Original', 'priority' => 'Low', 'status' => 'Approved']),
            'submitted_by' => $engineer->id,
            'status' => 'Pending',
        ]);

        // Approve as manager
        $this->actingAs($manager);
        $resp = $this->post("/pending-updates/{$pending->id}/review", ['action' => 'approve']);
        $resp->assertStatus(302);

        // Pending update marked approved and requirement updated
        $this->assertDatabaseHas('pending_updates', ['id' => $pending->id, 'status' => 'Approved']);
        $this->assertDatabaseHas('requirements', ['id' => $requirement->id, 'description' => 'Updated description', 'priority' => 'High']);

        // A new version should exist
        $this->assertDatabaseHas('requirement_versions', ['requirement_id' => $requirement->id, 'description' => 'Updated description']);

        // Now create another pending update to test rejection
        $pending2 = PendingUpdate::create([
            'requirement_id' => $requirement->id,
            'project_id' => $project->id,
            'updated_data' => json_encode(['description' => 'Another change', 'priority' => 'Medium']),
            'previous_data' => json_encode(['description' => 'Updated description', 'priority' => 'High', 'status' => 'Approved']),
            'submitted_by' => $engineer->id,
            'status' => 'Pending',
        ]);

        // Simulate manager rejecting
        $resp2 = $this->post("/pending-updates/{$pending2->id}/review", ['action' => 'reject']);
        $resp2->assertStatus(302);

        $this->assertDatabaseHas('pending_updates', ['id' => $pending2->id, 'status' => 'Rejected']);
        // Requirement status should be reverted to previous status (Approved)
        $this->assertDatabaseHas('requirements', ['id' => $requirement->id, 'status' => 'Approved']);
    }

    public function test_pending_resolution_approval_and_rejection()
    {
        $manager = User::factory()->create(['role' => 'project manager']);
        $engineer = User::factory()->create(['role' => 'software engineer']);

        $project = Project::create(['name' => 'Proj B', 'description' => 'B']);
        $feature = ProjectFeature::create(['project_id' => $project->id, 'category' => 'Core', 'feature' => 'F2']);

        $reqA = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => 'A',
            'description' => 'Req A',
            'category' => 'Core',
            'priority' => 'Medium',
            'status' => 'Approved',
            'author_id' => $engineer->id,
        ]);

        $reqB = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => 'B',
            'description' => 'Req B',
            'category' => 'Core',
            'priority' => 'Low',
            'status' => 'Approved',
            'author_id' => $engineer->id,
        ]);

        $conflict = Conflict::create([
            'requirement_a_id' => $reqA->id,
            'requirement_b_id' => $reqB->id,
            'summary' => 'Conflict between A and B',
            'severity' => 'Medium',
            'status' => 'Pending',
            'project_id' => $project->id,
            'resolution' => 'Prioritize One Over the Other',
        ]);

        // Create a pending resolution proposing to archive requirement B
        $pendingRes = PendingResolution::create([
            'conflict_id' => $conflict->id,
            'requirement_a_id' => $reqA->id,
            'requirement_b_id' => $reqB->id,
            'resolution' => 'Prioritize One Over the Other',
            'updated_data' => json_encode(['delete_requirement_id' => $reqB->id]),
            'proposed_by' => $engineer->id,
            'comments' => 'Propose to archive B',
            'status' => 'Pending',
        ]);

        $this->actingAs($manager);

        // Approve resolution
        $r1 = $this->post("/pending-resolutions/{$pendingRes->id}/review", ['action' => 'approve']);
        $r1->assertStatus(302);

        // Conflict marked resolved and pending resolution set to Approved
        $this->assertDatabaseHas('conflicts', ['id' => $conflict->id, 'status' => 'Resolved']);
        $this->assertDatabaseHas('pending_resolution', ['id' => $pendingRes->id, 'status' => 'Approved']);

        // Requirement B should be archived (status = Rejected)
        $this->assertDatabaseHas('requirements', ['id' => $reqB->id, 'status' => 'Rejected']);

        // Now test rejecting a pending resolution: create another pending resolution
        $pendingRes2 = PendingResolution::create([
            'conflict_id' => $conflict->id,
            'requirement_a_id' => $reqA->id,
            'requirement_b_id' => $reqB->id,
            'resolution' => 'Combine and Reword',
            'updated_data' => json_encode(['combine_into' => 'requirement_a', 'description' => 'Combined description']),
            'proposed_by' => $engineer->id,
            'comments' => 'Try combine',
            'status' => 'Pending',
        ]);

        $r2 = $this->post("/pending-resolutions/{$pendingRes2->id}/review", ['action' => 'reject']);
        $r2->assertStatus(302);

        // The pending resolution should be deleted by controller on rejection
        $this->assertDatabaseMissing('pending_resolution', ['id' => $pendingRes2->id]);
    }
}
