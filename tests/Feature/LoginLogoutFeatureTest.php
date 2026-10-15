<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginLogoutFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_login_access_and_logout()
    {
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('1234'),
            'role' => 'project manager',
        ]);

        // Guest is redirected to login for protected route
        $this->get('/view-project')->assertRedirect('/login');

        // Login
        $this->post('/login', [
            'email' => 'manager@example.com',
            'password' => '1234',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($manager);

        // Access protected page
        $this->get('/view-project')
            ->assertOk()
            ->assertSee('Project Overview');

        // Logout
        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();

        // Protected route redirects again after logout
        $this->get('/view-project')->assertRedirect('/login');
    }

    public function test_engineer_can_login_access_submit_and_logout()
    {
        $engineer = User::factory()->create([
            'email' => 'engineer@example.com',
            'password' => Hash::make('1234'),
            'role' => 'software engineer',
        ]);

        // Login
        $this->get('/login')->assertOk();

        $this->post('/login', [
            'email' => 'engineer@example.com',
            'password' => '1234',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($engineer);

        // Access engineer page
        $this->get('/submit-requirements')
            ->assertOk()
            ->assertSee('Submit Requirement');

        // Logout
        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }
}
