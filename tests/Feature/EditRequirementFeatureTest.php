<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EditRequirementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_edit_creates_pending_update_and_sets_pending()
    {
        $engineer = User::factory()->create([
            'email' => 'engineer@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        $project = Project::create(['name' => 'Project Y', 'description' => 'Test project Y']);

        $feature = ProjectFeature::create([
            'project_id' => $project->id,
            'category' => 'UI',
            'feature' => 'Dashboard',
        ]);

        // assign engineer to project
        $project->engineers()->sync([$engineer->id]);

        $requirement = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => $feature->feature,
            'description' => 'Original description',
            'category' => 'UI',
            'priority' => 'Low',
            'status' => 'Approved',
            'author_id' => $engineer->id,
        ]);

        $this->actingAs($engineer);

        $payload = [
            'description' => 'Engineer suggested change',
            'category' => 'UI',
            'priority' => 'High',
        ];

        $response = $this->put("/requirements/{$requirement->id}", $payload);
        // Controller redirects back after creating pending update
        $response->assertStatus(302);

        // Pending update created
        $this->assertDatabaseHas('pending_updates', [
            'requirement_id' => $requirement->id,
            'status' => 'Pending',
        ]);

        // Requirement status set to Pending
        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'status' => 'Pending',
        ]);
    }
}
