<?php

namespace App\Events\Booking;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingUpdated
{
    use Dispatchable, SerializesModels;

    public Booking $booking;

    public array $data;

    public function __construct(Booking $booking, array $data)
    {
        $this->booking = $booking;
        $this->data = $data;
    }
}
