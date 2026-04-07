<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\BookingCreated::class => [
            \App\Listeners\SendBookingCreatedNotification::class,
            \App\Listeners\SendTrainerBookingCreatedNotification::class,
        ],
        \App\Events\BookingCancelled::class => [
            \App\Listeners\SendBookingCancelledNotification::class,
            \App\Listeners\SendTrainerBookingCancelledNotification::class,
        ],
        \App\Events\BookingDeleted::class => [
            \App\Listeners\SendBookingDeletedNotification::class,
        ],
        \App\Events\BookingUpdated::class => [
            \App\Listeners\SendBookingUpdatedNotification::class,
            \App\Listeners\SendTrainerBookingUpdatedNotification::class,
        ],
        \App\Events\ProgrammeScheduleCancelled::class => [
            \App\Listeners\SendProgrammeScheduleCancelledNotification::class,
        ],
        \App\Events\ProgrammeScheduleRescheduled::class => [
            \App\Listeners\SendProgrammeScheduleRescheduledNotification::class,
        ],
        \App\Events\AnnouncementCreated::class => [
            \App\Listeners\SendAnnouncementCreatedNotification::class,
        ],
        \App\Events\AnnouncementUpdated::class => [
            \App\Listeners\SendAnnouncementUpdatedNotification::class,
        ],
        \App\Events\AnnouncementDeleted::class => [
            \App\Listeners\SendAnnouncementDeletedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
