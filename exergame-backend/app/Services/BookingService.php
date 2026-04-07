<?php

namespace App\Services;

use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeleted;
use App\Events\Booking\BookingUpdated;
use App\Models\Booking;
use App\Models\ProgrammeSchedule;
use Carbon\Carbon;
use Carbon\Unit;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    /**
     * Get booking counters for the current user
     * Returns: [total, upcoming, completed, cancelled]
     */
    public static function getBookingCounters($user = null)
    {
        $user = $user ?: auth()->user();
        $now = Carbon::now();

        // Get all bookings for user
        $bookings = Booking::with('schedule')
            ->where('user_id', $user->id)
            ->get();

        $total = $bookings->count();
        $upcoming = $bookings->filter(function ($booking) use ($now) {
            if ($booking->status !== 'confirmed') {
                return false;
            }
            $dt = Carbon::parse($booking->schedule->day.' '.$booking->schedule->start_time);

            return $dt->greaterThanOrEqualTo($now);
        })->count();
        $completed = $bookings->filter(function ($booking) use ($now) {
            $dt = Carbon::parse($booking->schedule->day.' '.$booking->schedule->start_time);

            return $dt->lessThan($now) && $booking->status === 'completed';
        })->count();
        $cancelled = $bookings->where('status', 'cancelled')->count();

        return [
            'total' => $total,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ];
    }

    /**
     * Get all bookings with their related data
     */
    public static function getBookings($request)
    {
        $user = auth()->user();

        $items = Booking::select('bookings.*')
            ->leftJoin('programme_schedules', 'bookings.schedule_id', '=', 'programme_schedules.id')
            ->where(function ($query) use ($user) {
                return $query->when($user->role == 'user', function ($query) use ($user) {
                    return $query->where('user_id', $user->id);

                })->when($user->role == 'trainer', function ($query) use ($user) {
                    return $query->where('programme_schedules.trainer_id', $user->id);
                });
            })
            ->when($request->expand, function ($query, $expand) {
                return $query->with(explode(',', $expand));
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->limit, function ($query, $limit) {
                return $query->limit($limit);
            })
            ->when($request->latest, function ($query, $latest) {
                if ((bool) $latest === true) {
                    return $query->where('programme_schedules.day', '>=', date('Y-m-d'))
                        ->where('programme_schedules.start_time', '>=', date('H:i:s'));

                } else {
                    $units = ['d' => Unit::Day, 'w' => Unit::Week, 'm' => Unit::Month];
                    [$number, $unit] = str_split($latest);
                    if (in_array($unit, ['d', 'w', 'm'])) {
                        return $query->whereBetween('programme_schedules.day', [
                            Carbon::now(),
                            Carbon::now()->add($units[$unit], (int) $number),
                        ]);
                    }
                }
            })
            ->when($request->date, function ($query, $date) {
                return $query->whereDate('programme_schedules.day', $date);
            })
            ->orderBy('programme_schedules.day', 'desc')
            ->orderBy('programme_schedules.start_time', 'desc')
            ->get();

        if ($request->count) {
            if ($request->count === 'schedule.bookings'
                && \Str::contains($request->expand, 'schedule')
            ) {
                $scheduleIds = $items->pluck('schedule_id')->unique();
                $schedules = ProgrammeSchedule::whereIn('id', $scheduleIds)
                    ->withCount(['bookings as total_bookings' => function ($query) {
                        $query->where('status', 'confirmed');
                    }])
                    ->get()
                    ->keyBy('id');
                $items = $items->map(function ($item) use ($schedules) {
                    $item->schedule->total_bookings = $schedules->get($item->schedule_id)->total_bookings;

                    return $item;
                });
            }
        }

        return $items;
    }

    /**
     * Get user's bookings
     */
    public static function getUserBookings(int $userId): Collection
    {
        return Booking::select('bookings.*')
            ->with(['schedule.programme', 'schedule.trainer', 'schedule.room'])
            ->join('programme_schedules', 'bookings.schedule_id', '=', 'programme_schedules.id')
            ->where('user_id', $userId)
            ->orderBy('programme_schedules.day', 'desc')
            ->orderBy('programme_schedules.start_time', 'desc')
            ->get();
    }

    /**
     * Get a booking by its ID
     */
    public static function getBookingById(int $id): Booking
    {
        return Booking::findOrFail($id);
    }

    /**
     * Create a new booking
     *
     * @param  array  $data  Already mapped data in snake_case format
     * @param  int  $userId  The ID of the user for whom the booking is being created.
     *
     * @throws \Exception
     */
    public static function createBooking(array $data): Booking
    {
        // Set booking data
        $bookingData = [
            'user_id' => auth()->id(),
            'schedule_id' => $data['schedule_id'],
            'status' => 'confirmed',
            'is_liability_waiver_accepted' => $data['is_liability_waiver_accepted'],
        ];

        $booking = Booking::create($bookingData);

        // Fire event
        event(new BookingCreated($booking));

        return $booking;
    }

    /**
     * Update a booking
     *
     * @throws \Exception
     */
    public static function updateBooking(Booking $booking, array $data): Booking
    {
        // If cancelling, add cancellation details
        if (isset($data['status']) && $data['status'] === 'cancelled') {
            $data['cancellation_at'] = Carbon::now();
        } else {
            $data['cancellation_at'] = null;
        }

        $booking->update($data);
        $updatedBooking = $booking->fresh();

        // Fire event
        event(new BookingUpdated($updatedBooking, $data));

        return $updatedBooking;
    }

    /**
     * Delete a booking
     */
    public static function deleteBooking(int $id): bool
    {
        $booking = Booking::findOrFail($id);
        $result = $booking->delete();

        // Fire event
        event(new BookingDeleted($booking));

        return $result;
    }
}
