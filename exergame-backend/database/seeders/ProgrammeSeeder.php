<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        \DB::table('programmes')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Get the first user as the creator (assuming there's at least one user)
        $firstUserId = \DB::table('users')->first()->id ?? 1;

        // Get game type IDs
        $cardioTypeId = \DB::table('game_types')->where('name', 'Cardio')->first()->id;
        $strengthTypeId = \DB::table('game_types')->where('name', 'Strength')->first()->id;
        $flexibilityTypeId = \DB::table('game_types')->where('name', 'Flexibility')->first()->id;
        $enduranceTypeId = \DB::table('game_types')->where('name', 'Endurance')->first()->id;
        $balanceTypeId = \DB::table('game_types')->where('name', 'Balance')->first()->id;
        $coordinationTypeId = \DB::table('game_types')->where('name', 'Coordination')->first()->id;

        \DB::table('programmes')->insert([
            [
                'type_id' => $cardioTypeId,
                'created_by' => $firstUserId,
                'name' => 'Cardio Blast Challenge',
                'synopsis' => 'High-intensity cardio workout designed to maximize calorie burn and improve cardiovascular health. Features fast-paced exercises with interactive floor projections.',
                'cover_image' => 'https://placehold.co/600x400/ff9f1c/ffffff?text=Cardio+Blast',
                'intensity_level' => 'high',
                'session_type' => 'group',
                'max_participants' => 15,
                'duration_minutes' => 45,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $strengthTypeId,
                'created_by' => $firstUserId,
                'name' => 'Strength Training Pro',
                'synopsis' => 'Comprehensive strength training program focusing on building muscle mass and improving overall body strength using bodyweight and resistance exercises.',
                'cover_image' => 'https://placehold.co/600x400/1a4d2e/ffffff?text=Strength+Pro',
                'intensity_level' => 'medium',
                'session_type' => 'group',
                'max_participants' => 12,
                'duration_minutes' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $flexibilityTypeId,
                'created_by' => $firstUserId,
                'name' => 'Yoga & Meditation',
                'synopsis' => 'Relaxing yoga session combined with mindfulness meditation to improve flexibility, balance, and mental well-being.',
                'cover_image' => 'https://placehold.co/600x400/20c997/ffffff?text=Yoga+Zen',
                'intensity_level' => 'low',
                'session_type' => 'group',
                'max_participants' => 20,
                'duration_minutes' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $enduranceTypeId,
                'created_by' => $firstUserId,
                'name' => 'HIIT Endurance Beast',
                'synopsis' => 'High-Intensity Interval Training designed to push your endurance limits with challenging exercises and short recovery periods.',
                'cover_image' => 'https://placehold.co/600x400/dc3545/ffffff?text=HIIT+Beast',
                'intensity_level' => 'high',
                'session_type' => 'group',
                'max_participants' => 10,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $balanceTypeId,
                'created_by' => $firstUserId,
                'name' => 'Balance Master',
                'synopsis' => 'Specialized program focusing on improving balance, stability, and core strength through various balance challenges and exercises.',
                'cover_image' => 'https://placehold.co/600x400/6f42c1/ffffff?text=Balance+Master',
                'intensity_level' => 'medium',
                'session_type' => 'single',
                'max_participants' => 8,
                'duration_minutes' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $coordinationTypeId,
                'created_by' => $firstUserId,
                'name' => 'Coordination Quest',
                'synopsis' => 'Fun and engaging coordination training using interactive games and exercises to improve hand-eye coordination and reaction time.',
                'cover_image' => 'https://placehold.co/600x400/fd7e14/ffffff?text=Coordination+Quest',
                'intensity_level' => 'medium',
                'session_type' => 'group',
                'max_participants' => 14,
                'duration_minutes' => 35,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $cardioTypeId,
                'created_by' => $firstUserId,
                'name' => 'Super Boost SB1 Beginner',
                'synopsis' => 'Entry-level cardio session structured to maximize calorie burn and cardiovascular output, perfect for beginners starting their fitness journey.',
                'cover_image' => 'https://placehold.co/600x400/28a745/ffffff?text=SB1+Beginner',
                'intensity_level' => 'low',
                'session_type' => 'group',
                'max_participants' => 18,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $cardioTypeId,
                'created_by' => $firstUserId,
                'name' => 'Super Boost SB1 Intermediate',
                'synopsis' => 'Intermediate level cardio session building on foundational skills, focusing on higher intensity and more complex movements.',
                'cover_image' => 'https://placehold.co/600x400/17a2b8/ffffff?text=SB1+Intermediate',
                'intensity_level' => 'medium',
                'session_type' => 'group',
                'max_participants' => 15,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type_id' => $cardioTypeId,
                'created_by' => $firstUserId,
                'name' => 'Super Boost SB1 Advanced',
                'synopsis' => 'High-level training for experienced users. This session challenges strength, endurance, and agility with advanced exercises and competitive elements.',
                'cover_image' => 'https://placehold.co/600x400/dc3545/ffffff?text=SB1+Advanced',
                'intensity_level' => 'high',
                'session_type' => 'group',
                'max_participants' => 12,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
