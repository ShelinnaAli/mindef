<?php

namespace App\Events\ProgrammeSchedule;

use App\Models\ProgrammeSchedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgrammeScheduleRescheduled
{
    use Dispatchable, SerializesModels;

    public ProgrammeSchedule $schedule;

    public array $data;

    public function __construct(ProgrammeSchedule $schedule, array $data)
    {
        $this->schedule = $schedule;
        $this->data = $data;
    }
}
