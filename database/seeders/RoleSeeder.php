<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(['administrator', 'customer'] as $role) {
            Role::create([
                'name'  => $role,
                'url'   => $role,
            ]);
        }
    }
}
