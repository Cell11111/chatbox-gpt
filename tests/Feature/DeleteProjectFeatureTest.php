<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\Requirement;
use App\Models\Defect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_delete_project_and_related_records_are_removed()
    {
        // Create users
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('1234'),
            'role' => 'project manager',
        ]);

        $engineer = User::factory()->create([
            'email' => 'eng@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        // Create a project and assign engineer
        $project = Project::create([
            'name' => 'Obsolete Project',
            'description' => 'To be deleted',
        ]);
        $project->engineers()->sync([$engineer->id]);

        // Create a feature, a requirement, and a defect under the project
        $feature = ProjectFeature::create([
            'project_id' => $project->id,
            'category' => 'Core',
            'feature' => 'Login',
        ]);

        $requirement = Requirement::create([
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => 'User can login',
            'description' => 'Allow users to login using email/password',
            'category' => 'Functional',
            'priority' => 'High',
            'status' => 'Approved',
            'author_id' => $engineer->id,
            'number' => 1,
        ]);

        $defect = Defect::create([
            'requirement_id' => $requirement->id,
            'type' => 'Ambiguity',
            'description' => 'Password rules unclear',
            'suggestion' => 'Specify password length',
        ]);

        // Confirm records exist
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('project_features', ['id' => $feature->id]);
        $this->assertDatabaseHas('requirements', ['id' => $requirement->id]);
        $this->assertDatabaseHas('defects', ['id' => $defect->id]);

        // Delete the project as manager
        $this->actingAs($manager);
        $response = $this->deleteJson("/projects/{$project->id}");
        $response->assertOk();
        $response->assertJson(['message' => 'Project deleted successfully.']);

        // Assert project and related records removed (soft or hard delete depending on implementation)
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_features', ['id' => $feature->id]);
        $this->assertDatabaseMissing('requirements', ['id' => $requirement->id]);
        $this->assertDatabaseMissing('defects', ['id' => $defect->id]);
    }
}
