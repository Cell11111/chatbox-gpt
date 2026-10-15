<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\Requirement;
use App\Models\RequirementVersion;

class ProjectRollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_rollback_reverts_requirements_to_target_version()
    {
        $manager = User::factory()->create(['role' => 'project manager']);

        $project = Project::create(['name' => 'RollbackProj', 'description' => 'For rollback']);
        $feature = ProjectFeature::create(['project_id' => $project->id, 'category' => 'Core', 'feature' => 'Feat']);

        // Create two requirements with version history
        $req1 = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => 'Req One',
            'description' => 'Original Desc 1',
            'category' => 'Core',
            'priority' => 'Low',
            'status' => 'Approved',
            'author_id' => $manager->id,
        ]);

        RequirementVersion::create([
            'requirement_id' => $req1->id,
            'project_id' => $project->id,
            'version' => 1,
            'title' => $req1->title,
            'description' => 'Original Desc 1',
            'category' => 'Core',
            'priority' => 'Low',
            'status' => 'Approved',
            'feature_id' => $feature->id,
            'author_id' => $manager->id,
        ]);

        // Make a second version (simulate change)
        $req1->update(['description' => 'Changed Desc 1', 'priority' => 'High']);
        RequirementVersion::create([
            'requirement_id' => $req1->id,
            'project_id' => $project->id,
            'version' => 2,
            'title' => $req1->title,
            'description' => 'Changed Desc 1',
            'category' => 'Core',
            'priority' => 'High',
            'status' => 'Approved',
            'feature_id' => $feature->id,
            'author_id' => $manager->id,
        ]);

        // Second requirement
        $req2 = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => 'Req Two',
            'description' => 'Original Desc 2',
            'category' => 'Core',
            'priority' => 'Medium',
            'status' => 'Approved',
            'author_id' => $manager->id,
        ]);

        RequirementVersion::create([
            'requirement_id' => $req2->id,
            'project_id' => $project->id,
            'version' => 1,
            'title' => $req2->title,
            'description' => 'Original Desc 2',
            'category' => 'Core',
            'priority' => 'Medium',
            'status' => 'Approved',
            'feature_id' => $feature->id,
            'author_id' => $manager->id,
        ]);

        // Change req2 to create version 2
        $req2->update(['description' => 'Changed Desc 2', 'priority' => 'Low']);
        RequirementVersion::create([
            'requirement_id' => $req2->id,
            'project_id' => $project->id,
            'version' => 2,
            'title' => $req2->title,
            'description' => 'Changed Desc 2',
            'category' => 'Core',
            'priority' => 'Low',
            'status' => 'Approved',
            'feature_id' => $feature->id,
            'author_id' => $manager->id,
        ]);

        // Now rollback the whole project to version 1
        $this->actingAs($manager);
        $resp = $this->post('/project-rollback', ['project_id' => $project->id, 'target_version' => 1]);
        $resp->assertStatus(200);

        $resp->assertJson(['success' => true]);

        // Both requirements should now have their original descriptions
        $this->assertDatabaseHas('requirements', ['id' => $req1->id, 'description' => 'Original Desc 1', 'priority' => 'Low']);
        $this->assertDatabaseHas('requirements', ['id' => $req2->id, 'description' => 'Original Desc 2', 'priority' => 'Medium']);

        // A new version should have been created for each rolled requirement (version 3)
        $this->assertDatabaseHas('requirement_versions', ['requirement_id' => $req1->id, 'version' => 3, 'description' => 'Original Desc 1']);
        $this->assertDatabaseHas('requirement_versions', ['requirement_id' => $req2->id, 'version' => 3, 'description' => 'Original Desc 2']);
    }
}
