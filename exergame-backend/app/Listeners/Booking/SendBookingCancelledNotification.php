<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingCancelled;
use App\Models\UserNotification;

class SendBookingCancelledNotification
{
    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event)
    {
        $booking = $event->booking;
        if (! $booking) {
            return;
        }

        $schedule = $booking->schedule;
        $programme = $schedule->programme;
        $room = $schedule->room;
        $trainer = $schedule->trainer;

        $reason = $event->reason;
        UserNotification::create([
            'user_id' => $booking->user_id,
            'announcement_id' => null,
            'type' => 'alert',
            'title' => 'Booking Cancelled',
            'message' => 'Your booking session for "'.($programme->name ?? 'N/A').'" has been cancelled.'.
            '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
            '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
            '<br>Location: '.($room ? $room->name : 'N/A').
            '<br>Trainer: '.($trainer ? $trainer->name : 'N/A').
            '<br>Reason: '.($reason ?? 'N/A'),
            'is_read' => false,
        ]);
    }
}
