<?php

namespace App\Services;

use App\Events\ProgrammeSchedule\ProgrammeScheduleCancelled;
use App\Events\ProgrammeSchedule\ProgrammeScheduleRescheduled;
use App\Models\Booking;
use App\Models\ProgrammeSchedule;
use Carbon\Carbon;
use Carbon\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProgrammeScheduleService
{
    /**
     * Get total unique programmes for a trainer
     */
    public static function getTrainerTotalProgrammes($trainerId): int
    {
        return ProgrammeSchedule::active()
            ->where('trainer_id', $trainerId)
            ->distinct('programme_id')
            ->count('programme_id');
    }

    /**
     * Get average completed bookings per session for a trainer
     */
    public static function getTrainerAvgAttendance(): float
    {
        $user = auth()->user();
        $scheduleIds = ProgrammeSchedule::active()
            ->where('trainer_id', $user->id)
            ->pluck('id');

        $completedBookings = Booking::completed()
            ->whereIn('schedule_id', $scheduleIds)
            ->count();

        $totalSessions = count($scheduleIds);

        return $totalSessions > 0
            ? round($completedBookings / $totalSessions, 2)
            : 0;
    }

    /**
     * Get total bookings for a trainer's programme schedules
     */
    public static function getTrainerTotalBookings(): int
    {
        $user = auth()->user();
        return Booking::completed()
            ->whereIn('schedule_id', function ($query) use ($user) {
                return $query->select('id')
                    ->from('programme_schedules')
                    ->whereColumn('programme_schedules.id', 'bookings.schedule_id')
                    ->where('is_cancelled', false)
                    ->where('trainer_id', $user->id);
            })
            ->count();
    }

    /**
     * Get counters for programme schedules (active and cancelled)
     * Returns: [active, cancelled]
     */
    public static function getScheduleCounters($request): array
    {
        $user = auth()->user();
        $schedules = ProgrammeSchedule::query()
            ->when($user->role === 'trainer', function ($query) use ($user) {
                return $query->where('trainer_id', $user->id);
            })
            ->when($request->completed, function ($query) {
                return $query->where(function ($q) {
                    $today = date('Y-m-d');
                    $q->where('day', '<', $today)
                      ->orWhere(function ($q2) use ($today) {
                        $now = date('H:i:s');
                        $q2->where('day', $today)
                            ->where('end_time', '<', $now);
                    });
                });
            })
            ->get();
        $active = $schedules->where('is_cancelled', false)->count();
        $cancelled = $schedules->where('is_cancelled', true)->count();

        return [
            'active' => $active,
            'cancelled' => $cancelled,
        ];
    }

    /**
     * Get all programme schedules
     */
    public static function getSchedules($request)
    {
        $user = auth()->user();

        $query = ProgrammeSchedule::query()
            ->when($user->role === 'trainer', function ($query) use ($user) {
                return $query->where('trainer_id', $user->id);
            })
            ->when($request->expand, function ($query, $expand) {
                return $query->with(explode(',', $expand));
            })
            ->when($request->count, function ($query) use ($request) {
                return $query->withCount($request->count);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    return $query->active();
                }
            })
            ->when($request->latest, function ($query, $latest) {
                if ((bool) $latest === true) {
                    return $query->where('day', '>=', date('Y-m-d'))
                        ->where('start_time', '>=', date('H:i:s'));

                } else {
                    $units = ['d' => Unit::Day, 'w' => Unit::Week, 'm' => Unit::Month];
                    [$number, $unit] = str_split($latest);
                    if (in_array($unit, ['d', 'w', 'm'])) {
                        return $query->whereBetween('day', [
                            Carbon::now(),
                            Carbon::now()->add($units[$unit], (int) $number),
                        ]);
                    }
                }
            })
            ->when($request->date, function ($query, $date) {
                return $query->whereDate('day', $date);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('day', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('day', '<=', $dateTo);
            })
            ->when($request->date_range, function ($query, $dateRange) {
                $dates = explode(',', $dateRange);
                if (count($dates) === 2) {
                    return $query->whereBetween('day', [$dates[0], $dates[1]]);
                }
            })
            ->when($request->sorts, function ($query, $sorts) {
                $sortArray = explode(',', $sorts);
                foreach ($sortArray as $sort) {
                    [$field, $direction] = explode(':', $sort);
                    $query->orderBy(\Str::snake($field), $direction);
                }
            }, function ($query) {
                $query->orderBy('day', 'desc')
                    ->orderBy('start_time', 'desc');
            });

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->get('limit', 15); // Default to 15 items per page
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Retrieves the list of users that have bookings for a specific schedule.
     *
     * @param  int  $scheduleId  The ID of the schedule whose bookings are to be fetched.
     * @return array An array of users.
     */
    public static function getUserBookingsByScheduleId(int $scheduleId): Collection
    {
        return ProgrammeSchedule::select('users.id', 'users.name', 'users.phone', 'bookings.status')
            ->join('bookings', 'programme_schedules.id', '=', 'bookings.schedule_id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->where('programme_schedules.id', $scheduleId)
            ->get();
    }

    /**
     * Get programme schedules for a specific month and year
     */
    public static function getMonthlySchedules(int $year, int $month): Collection
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return ProgrammeSchedule::query()
            ->with([
                'programme.gameType',
                'trainer:id,name',
                'room:id,name',
            ])
            ->withCount(['bookings as bookingsCount' => function ($query) {
                $query->where('status', 'confirmed');
            }])
            ->whereBetween('day', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Create a new programme schedule
     */
    public static function createSchedule(array $data): ProgrammeSchedule
    {
        return ProgrammeSchedule::create($data);
    }

    /**
     * Update a programme schedule
     */
    public static function updateSchedule(int $id, array $data): ?ProgrammeSchedule
    {
        $schedule = ProgrammeSchedule::findOrFail($id);
        $schedule->update($data);

        // Fire event if schedule is cancelled
        if (isset($data['is_cancelled']) && $data['is_cancelled']) {
            event(new ProgrammeScheduleCancelled($schedule));
        } elseif (
            (isset($data['day']) && isset($data['start_time']) && isset($data['end_time'])) &&
            (! isset($data['is_cancelled']) || ! $data['is_cancelled'])
        ) {
            event(new ProgrammeScheduleRescheduled($schedule, $data));
        }

        return $schedule;
    }

    /**
     * Determines if a programme schedule is available to be booked.
     *
     * @param  int  $id  The ID of the programme schedule to check.
     * @return bool True if the schedule is available for booking, false otherwise.
     */
    public static function isAvailableToBooked(int $id): bool
    {
        $schedule = ProgrammeSchedule::active()->findOrFail($id);
        $currentBookings = Booking::confirmed()
            ->where('schedule_id', $id)
            ->count();

        return ! $schedule->is_cancelled && $currentBookings < $schedule->programme->max_participants;
    }
}
