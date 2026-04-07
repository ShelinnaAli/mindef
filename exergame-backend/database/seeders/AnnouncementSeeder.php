<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        // Seed announcement_types
        DB::table('announcement_types')->insert([
            ['id' => 1, 'name' => 'info', 'color' => 'secondary', 'icon' => 'fa-bullhorn'],
            ['id' => 2, 'name' => 'alert', 'color' => 'warning', 'icon' => 'fa-exclamation-circle'],
            ['id' => 3, 'name' => 'general', 'color' => 'warning', 'icon' => 'fa-star'],
            ['id' => 4, 'name' => 'system', 'color' => 'dark', 'icon' => 'fa-tools'],
        ]);

        // Seed announcements
        DB::table('announcements')->insert([
            [
                'id' => 1,
                'type_id' => 1,
                'title' => 'Gym Holiday Hours Update',
                'content' => 'Please note new operating hours for the upcoming public holiday on June 17th: 9:00 AM - 5:00 PM.',
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'type_id' => 2,
                'title' => 'Equipment Maintenance Scheduled',
                'content' => 'Treadmills 1-5 will be out of service for maintenance on June 20th, from 10:00 AM to 2:00 PM.',
                'is_active' => 1,
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay(),
            ],
            [
                'id' => 3,
                'type_id' => 3,
                'title' => 'New Trainer - Emily Chen',
                'content' => 'Welcome our new yoga and flexibility trainer, Emily Chen! Check her schedule in the Programmes section.',
                'is_active' => 1,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'id' => 4,
                'type_id' => 4,
                'title' => 'System Update',
                'content' => 'A new system update is scheduled for June 28th. Expect minor service interruptions. Check our website for more details.',
                'is_active' => 1,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
        ]);
    }
}
