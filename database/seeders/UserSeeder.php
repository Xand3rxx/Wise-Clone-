<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Administrative user account
        User::create([
            'role_id'           => 1,
            'name'              => 'Wise(Clone)',
            'email'             => 'admin@wiseclone.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create global user for easy test
        User::create([
            'role_id'           => 2,
            'name'              => 'Anthony Joboy',
            'email'             => 'user@wiseclone.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Generate 20 otther users
        User::factory(20)->create();
    }
}
