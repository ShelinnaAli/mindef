<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        \DB::table('game_types')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        \DB::table('game_types')->insert([
            [
                'name' => 'Cardio',
                'is_active' => true,
            ],
            [
                'name' => 'Strength',
                'is_active' => true,
            ],
            [
                'name' => 'Flexibility',
                'is_active' => true,
            ],
            [
                'name' => 'Endurance',
                'is_active' => true,
            ],
            [
                'name' => 'Balance',
                'is_active' => true,
            ],
            [
                'name' => 'Coordination',
                'is_active' => true,
            ],
        ]);
    }
}
