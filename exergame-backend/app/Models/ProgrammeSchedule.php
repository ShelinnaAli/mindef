<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammeSchedule extends Model
{
    protected $fillable = [
        'programme_id',
        'trainer_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
        'is_cancelled',
        'cancellation_reason',
    ];

    protected $casts = [
        'is_cancelled' => 'boolean',
    ];

    /* protected $appends = [
        'total_bookings',
    ]; */

    /**
     * Get the programme that owns the schedule.
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Get the trainer that owns the schedule.
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Get the room that owns the schedule.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the bookings for this schedule.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    // create scope for active schedules
    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }

    /**
     * Get the total number of bookings for this schedule.
     *
     * @return int
     */
    /* public function getTotalBookingsAttribute()
    {
        return $this->bookings()->where('status', '=', 'confirmed')->count();
    } */
}
