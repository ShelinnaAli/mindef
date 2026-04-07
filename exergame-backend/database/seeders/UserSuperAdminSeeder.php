<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'id' => 1
        ], [
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('Password@1234'),
            'role' => 'superadmin',
            'scheme_id' => 1,
            'phone' => '0000000000',
            'birth_year' => 1970,
            'gender' => 'male',
            'is_active' => true,
        ]);
    }
}
