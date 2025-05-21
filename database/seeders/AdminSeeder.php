<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator with full access']
        );
        
        // Create project manager role
        Role::firstOrCreate(
            ['name' => 'project_manager'],
            ['description' => 'Can manage assigned projects and tasks']
        );
        
        // Create client role
        Role::firstOrCreate(
            ['name' => 'client'],
            ['description' => 'Client access to view projects and communicate']
        );
        
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@timetracker.com'],
            [
                'name' => 'System Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'), // Change this in production!
                'role_id' => $adminRole->id,
            ]
        );
    }
}