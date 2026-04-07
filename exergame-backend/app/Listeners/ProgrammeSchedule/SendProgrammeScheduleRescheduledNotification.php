<?php

namespace App\Listeners\ProgrammeSchedule;

use App\Events\ProgrammeSchedule\ProgrammeScheduleRescheduled;
use App\Models\UserNotification;

class SendProgrammeScheduleRescheduledNotification
{
    /**
     * Handle the event.
     */
    public function handle(ProgrammeScheduleRescheduled $event)
    {
        $schedule = $event->schedule;
        if (! $schedule) {
            return;
        }

        $programme = $schedule->programme;
        $room = $schedule->room;
        $trainer = $schedule->trainer;
        // Notify trainer
        if ($trainer) {
            UserNotification::create([
                'user_id' => $trainer->id,
                'announcement_id' => null,
                'type' => 'alert',
                'title' => 'Session Rescheduled',
                'message' => 'Your programme session for "'.($programme->name ?? 'N/A').'"  has been rescheduled.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A'),
                'is_read' => false,
            ]);
        }
        // Notify users who booked this schedule
        $userIds = $schedule->bookings()->pluck('user_id')->unique();
        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'announcement_id' => null,
                'type' => 'alert',
                'title' => 'Session Rescheduled',
                'message' => 'A programme session you booked for "'.($programme->name ?? 'N/A').'" has been rescheduled.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A').
                '<br>Trainer: '.($trainer ? $trainer->name : 'N/A'),
                'is_read' => false,
            ]);
        }
    }
}
