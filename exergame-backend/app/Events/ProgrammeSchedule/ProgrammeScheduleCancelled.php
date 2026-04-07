<?php

namespace App\Events\ProgrammeSchedule;

use App\Models\ProgrammeSchedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgrammeScheduleCancelled
{
    use Dispatchable, SerializesModels;

    public ProgrammeSchedule $schedule;

    public function __construct(ProgrammeSchedule $schedule)
    {
        $this->schedule = $schedule;
    }
}
