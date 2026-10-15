<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 1234,
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Software Engineer',
            'email' => 'engineer@example.com',
            'password' => 1234,
            'role' => 'software engineer',
        ]);

        User::create([
            'name' => 'Software Engineer 1',
            'email' => 'engineer1@example.com',
            'password' => 1234,
            'role' => 'software engineer',
        ]);

        User::create([
            'name' => 'Software Engineer 2',
            'email' => 'engineer2@example.com',
            'password' => 1234,
            'role' => 'software engineer',
        ]);

        User::create([
            'name' => 'Project Manager',
            'email' => 'manager@example.com',
            'password' => 1234,
            'role' => 'project manager',
        ]);

        User::create([
            'name' => 'Stakeholder',
            'email' => 'stakeholder@example.com',
            'password' => 1234,
            'role' => 'stakeholder',
        ]);
    }
}

