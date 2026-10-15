<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessControlFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_protected_pages()
    {
        $protected = [
            '/view-project',
            '/submit-requirements',
            '/review-changes',
            '/view-conflict',
            '/review-defects',
            '/change-log',
        ];

        foreach ($protected as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }

    public function test_authenticated_engineer_can_access_core_pages()
    {
        $engineer = User::factory()->create([
            'email' => 'engineer@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        $this->post('/login', [
            'email' => 'engineer@example.com',
            'password' => '1234',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($engineer);

        $this->get('/view-project')->assertOk();
        $this->get('/submit-requirements')->assertOk();
        $this->get('/view-conflict')->assertOk();
        $this->get('/review-defects')->assertOk();
        $this->get('/change-log')->assertOk();
    }

    public function test_authenticated_manager_can_access_review_changes()
    {
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('1234'),
            'role' => 'project manager',
        ]);

        $this->post('/login', [
            'email' => 'manager@example.com',
            'password' => '1234',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($manager);

        $this->get('/review-changes')->assertOk();
    }
}
