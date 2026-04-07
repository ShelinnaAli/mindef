<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Exergame Studio',
                'capacity' => 100,
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Spin Studio',
                'capacity' => 100,
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Wellness Studio',
                'capacity' => 100,
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Main Gym',
                'capacity' => 100,
                'description' => null,
                'is_active' => true,
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }

        $this->command->info('Rooms seeded successfully!');
    }
}
