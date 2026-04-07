<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingCreated;
use App\Models\UserNotification;

class SendBookingCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event)
    {
        $booking = $event->booking;
        if (! $booking) {
            return;
        }

        $schedule = $booking->schedule;
        $programme = $schedule->programme;
        $room = $schedule->room;
        $trainer = $schedule->trainer;

        UserNotification::create([
            'user_id' => $booking->user_id,
            'announcement_id' => null,
            'type' => 'reminder',
            'title' => 'Booking Created',
            'message' => 'Your booking session for "'.($programme->name ?? 'N/A').'" has been created successfully.'.
            '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
            '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
            '<br>Location: '.($room ? $room->name : 'N/A').
            '<br>Trainer: '.($trainer ? $trainer->name : 'N/A'),
            'is_read' => false,
        ]);
    }
}
