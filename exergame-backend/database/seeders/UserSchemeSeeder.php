<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSchemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        \DB::table('user_schemes')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        \DB::table('user_schemes')->insert([[
            'name' => 'DXO',
            'description' => 'DXO',
            'is_active' => true,
        ], [
            'name' => 'Military',
            'description' => 'Military',
            'is_active' => true,
        ]]);
    }
}
