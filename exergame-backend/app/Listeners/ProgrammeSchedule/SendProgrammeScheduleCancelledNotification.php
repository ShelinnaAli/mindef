<?php

namespace App\Listeners\ProgrammeSchedule;

use App\Events\ProgrammeSchedule\ProgrammeScheduleCancelled;
use App\Models\UserNotification;

class SendProgrammeScheduleCancelledNotification
{
    /**
     * Handle the event.
     */
    public function handle(ProgrammeScheduleCancelled $event)
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
                'title' => 'Schedule Cancelled',
                'message' => 'Your programme schedule for "'.($programme->name ?? 'N/A').'" has been cancelled.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A').
                '<br>Reason: '.($schedule ? $schedule->cancellation_reason : 'N/A'),
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
                'title' => 'Schedule Cancelled',
                'message' => 'A programme schedule you booked for "'.($programme->name ?? 'N/A').'" has been cancelled.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A').
                '<br>Trainer: '.($trainer ? $trainer->name : 'N/A').
                '<br>Reason: '.($schedule ? $schedule->cancellation_reason : 'N/A'),
                'is_read' => false,
            ]);
        }
    }
}
