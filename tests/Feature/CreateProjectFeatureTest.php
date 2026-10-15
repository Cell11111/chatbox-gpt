<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_project_and_assign_engineers()
    {
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('1234'),
            'role' => 'project manager',
        ]);

        $engineerA = User::factory()->create([
            'email' => 'eng_a@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);
        $engineerB = User::factory()->create([
            'email' => 'eng_b@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        $payload = [
            'name' => 'Project Phoenix',
            'description' => 'Replatform initiative',
            'engineers' => [$engineerA->id, $engineerB->id],
        ];

        $this->actingAs($manager);

        $response = $this->postJson('/project-store', $payload)
            ->assertOk()
            ->assertJsonStructure(['project_id']);

        $projectId = $response->json('project_id');
        $this->assertNotEmpty($projectId);

        $project = Project::findOrFail($projectId);
        $this->assertSame('Project Phoenix', $project->name);
        $this->assertSame('Replatform initiative', $project->description);
        $this->assertEquals(2, $project->engineers()->count());
        $this->assertTrue($project->engineers->pluck('id')->contains($engineerA->id));
        $this->assertTrue($project->engineers->pluck('id')->contains($engineerB->id));
    }
}
