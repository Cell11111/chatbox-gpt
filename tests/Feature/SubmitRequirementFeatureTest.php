<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectFeature;
use App\Models\Requirement;
use App\Models\RequirementVersion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmitRequirementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_can_submit_requirement_for_assigned_project_feature()
    {
        $engineer = User::factory()->create([
            'email' => 'engineer@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        // Create project and feature
        $project = Project::create([
            'name' => 'Alpha Project',
            'description' => 'Alpha',
        ]);

        $feature = ProjectFeature::create([
            'project_id' => $project->id,
            'category' => 'Functional',
            'feature' => 'User Authentication',
        ]);

        // Assign engineer to the project
        $project->engineers()->sync([$engineer->id]);

        $payload = [
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'title' => $feature->feature,
            'category' => 'Functional',
            'priority' => 'High',
            'description' => 'Users must be able to register and login securely.'
        ];

        $this->actingAs($engineer);

        $response = $this->postJson('/save-requirements', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'requirement_id']);

        $reqId = $response->json('requirement_id');
        $this->assertNotEmpty($reqId);

        $this->assertDatabaseHas('requirements', [
            'id' => $reqId,
            'project_id' => $project->id,
            'feature_id' => $feature->id,
            'description' => 'Users must be able to register and login securely.',
            'priority' => 'High',
        ]);

        $this->assertDatabaseHas('requirement_versions', [
            'requirement_id' => $reqId,
            'version' => 1,
        ]);
    }
}
