<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingUpdated;
use App\Models\UserNotification;

class SendTrainerBookingUpdatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BookingUpdated $event)
    {
        $booking = $event->booking;
        if (! $booking) {
            return;
        }

        $schedule = $booking->schedule;
        $programme = $schedule->programme;
        $room = $schedule->room;
        $trainer = $schedule->trainer;
        $totalBookings = $schedule->bookings()->count();
        $maxParticipants = $programme ? $programme->max_participants : 0;

        if ($trainer) {
            $message = 'A booking for "'.($programme->name ?? 'N/A').'" has been updated.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A');
            if ($programme->session_type === 'group') {
                $message .= '<br>Group: '.$totalBookings.'/'.$maxParticipants;
            }
            UserNotification::create([
                'user_id' => $trainer->id,
                'announcement_id' => null,
                'type' => 'alert',
                'title' => 'Booking Updated',
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}
