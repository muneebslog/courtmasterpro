<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@event.com',
            'password' => 'Admin@12345',
        ]);

        //create a project with owner as admin
        $admin = User::where('email', 'admin@event.com')->first();
        $project = \App\Models\Project::create([
            'owner_id' => $admin->id,
            'name' => 'Digital Sports Management',
        ]);
    }
}
