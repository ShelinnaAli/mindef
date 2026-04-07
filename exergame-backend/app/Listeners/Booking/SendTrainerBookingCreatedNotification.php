<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingCreated;
use App\Models\UserNotification;

class SendTrainerBookingCreatedNotification
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
        $totalBookings = $schedule->bookings()->count();
        $maxParticipants = $programme ? $programme->max_participants : 0;

        if ($trainer) {
            $message = 'A new booking "'.($programme->name ?? 'N/A').'" has been created for your session.'.
                '<br>Date: '.($schedule ? date('Y-m-d', strtotime($schedule->day)) : 'N/A').
                '<br>Time: '.($schedule ? date('H:i', strtotime($schedule->start_time)) : 'N/A').
                '<br>Location: '.($room ? $room->name : 'N/A');

            if ($programme->session_type === 'group') {
                $message .= '<br>Group: '.$totalBookings.'/'.$maxParticipants;
            }

            UserNotification::create([
                'user_id' => $trainer->id,
                'announcement_id' => null,
                'type' => 'reminder',
                'title' => 'New Booking',
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}
