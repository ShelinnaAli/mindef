<?php

namespace Database\Seeders;

use App\Models\Programme;
use App\Models\ProgrammeSchedule;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProgrammeScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create some rooms if they don't exist
        $rooms = [];
        $roomNames = ['Studio A', 'Studio B', 'Main Hall', 'Yoga Room'];

        foreach ($roomNames as $roomName) {
            $room = Room::firstOrCreate(['name' => $roomName]);
            $rooms[] = $room;
        }

        // Get some programmes and users (trainers)
        $programmes = Programme::where('is_active', true)->take(4)->get();
        $trainers = User::take(3)->get();

        if ($programmes->isEmpty() || $trainers->isEmpty()) {
            $this->command->warn('Please ensure you have programmes and users seeded first.');

            return;
        }

        // Create schedule data for the current month and next month
        $currentDate = Carbon::now()->startOfMonth();

        $schedules = [
            // Current month schedules
            [
                'programme_id' => $programmes[0]->id,
                'trainer_id' => $trainers[0]->id,
                'room_id' => $rooms[0]->id,
                'day' => $currentDate->copy()->addDays(4)->toDateString(), // 5th of current month
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'is_cancelled' => false,
            ],
            [
                'programme_id' => $programmes[1]->id,
                'trainer_id' => $trainers[1]->id,
                'room_id' => $rooms[1]->id,
                'day' => $currentDate->copy()->addDays(11)->toDateString(), // 12th of current month
                'start_time' => '18:00:00',
                'end_time' => '19:30:00',
                'is_cancelled' => false,
            ],
            [
                'programme_id' => $programmes[2]->id,
                'trainer_id' => $trainers[2]->id,
                'room_id' => $rooms[2]->id,
                'day' => $currentDate->copy()->addDays(11)->toDateString(), // 12th of current month
                'start_time' => '08:00:00',
                'end_time' => '09:00:00',
                'is_cancelled' => false,
            ],
            [
                'programme_id' => $programmes[3]->id ?? $programmes[0]->id,
                'trainer_id' => $trainers[0]->id,
                'room_id' => $rooms[3]->id,
                'day' => $currentDate->copy()->addDays(17)->toDateString(), // 18th of current month
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'is_cancelled' => true,
                'cancellation_reason' => 'Trainer unavailable due to emergency',
            ],
            [
                'programme_id' => $programmes[0]->id,
                'trainer_id' => $trainers[0]->id,
                'room_id' => $rooms[0]->id,
                'day' => $currentDate->copy()->addDays(24)->toDateString(), // 25th of current month
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'is_cancelled' => false,
            ],

            // Next month schedules
            [
                'programme_id' => $programmes[1]->id,
                'trainer_id' => $trainers[1]->id,
                'room_id' => $rooms[1]->id,
                'day' => $currentDate->copy()->addMonth()->addDays(2)->toDateString(), // 3rd of next month
                'start_time' => '10:00:00',
                'end_time' => '11:30:00',
                'is_cancelled' => false,
            ],
            [
                'programme_id' => $programmes[2]->id,
                'trainer_id' => $trainers[2]->id,
                'room_id' => $rooms[0]->id,
                'day' => $currentDate->copy()->addMonth()->addDays(9)->toDateString(), // 10th of next month
                'start_time' => '19:00:00',
                'end_time' => '20:00:00',
                'is_cancelled' => false,
            ],
        ];

        foreach ($schedules as $schedule) {
            ProgrammeSchedule::create($schedule);
        }

        $this->command->info('Programme schedules seeded successfully!');
    }
}
