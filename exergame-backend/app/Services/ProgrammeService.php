<?php

namespace App\Services;

use App\Models\Programme;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ProgrammeService
{
    /**
     * Get all programmes with their game types (both active and inactive)
     */
    public static function  getProgrammes($request)
    {
        $query = Programme::query()
            ->when($request->expand, function ($query, $expand) {
                return $query->with(explode(',', $expand));
            })
            ->when($request->isActive, function ($query, $isActive) {
                return $query->where('is_active', $isActive);
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('created_by', $userId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('synopsis', 'LIKE', "%{$search}%")
                      ->orWhere('intensity_level', 'LIKE', "%{$search}%")
                      ->orWhere('session_type', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->sorts, function ($query, $sorts) {
                $sortArray = explode(',', $sorts);
                foreach ($sortArray as $sort) {
                    [$field, $direction] = explode(':', $sort);
                    $query->orderBy(\Str::snake($field), $direction);
                }
            }, function ($query) {
                $query->latest();
            });

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Get programme by ID with game type and created by user
     */
    public static function getProgrammeById(int $id): ?Programme
    {
        return Programme::findOrFail($id);
    }

    /**
     * Create a new programme
     *
     * @param  array  $data  Already mapped data in snake_case format
     */
    public static function createProgramme(array $data, ?int $createdBy = null): Programme
    {
        // Set default value for is_active if not provided
        if (! isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        if ($createdBy) {
            $data['created_by'] = $createdBy;
        }

        return Programme::create($data);
    }

    /**
     * Update an existing programme
     *
     * @param  array  $data  Already mapped data in snake_case format
     */
    public static function updateProgramme(int $id, array $data): ?Programme
    {
        $programme = Programme::findOrFail($id);
        $programme->update($data);

        return $programme;
    }

    /**
     * Delete a programme (soft delete by setting is_active to false)
     */
    public static function deleteProgramme(int $id): bool
    {
        $programme = Programme::findOrFail($id);

        return $programme->update(['is_active' => false]);
    }

    /**
     * Retrieves a list of popular programmes based on the given request parameters.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request containing filters or criteria for popular programmes.
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator A collection or paginated list of popular programmes.
     */
    public static function getPopularProgrammes($request)
    {
        $query = Programme::active()
            ->select(
                DB::raw('ANY_VALUE(programmes.id) AS id'),
                DB::raw('ANY_VALUE(programmes.name) AS name'),
                DB::raw('ANY_VALUE(programmes.synopsis) AS synopsis'),
                DB::raw('ANY_VALUE(programmes.cover_image) AS cover_image'),
                DB::raw('ANY_VALUE(programmes.intensity_level) AS intensity_level'),
                DB::raw('ANY_VALUE(programmes.session_type) AS session_type'),
                DB::raw('ANY_VALUE(programmes.max_participants) AS max_participants'),
                DB::raw('ANY_VALUE(programmes.duration_minutes) AS duration_minutes'),
                DB::raw('COUNT(bookings.id) AS total_bookings')
            )
            ->with([
                'schedules' => fn ($q) => $q->orderBy('day', 'desc')->orderBy('start_time', 'desc'),
                'schedules.trainer:id,name',
                'schedules.room:id,name',
                'gameType:id,name',
            ])
            ->join('programme_schedules', 'programme_schedules.programme_id', '=', 'programmes.id')
            ->join('bookings', 'bookings.schedule_id', '=', 'programme_schedules.id')
            ->where('programme_schedules.is_cancelled', false)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('programmes.name', 'LIKE', "%{$search}%")
                      ->orWhere('programmes.synopsis', 'LIKE', "%{$search}%")
                      ->orWhere('programmes.intensity_level', 'LIKE', "%{$search}%")
                      ->orWhere('programmes.session_type', 'LIKE', "%{$search}%");
                });
            })
            ->having('total_bookings', '>', 0)
            ->groupBy('programmes.id');

        // Apply sorting
        $sortField = $request->sort ?? 'total_bookings';
        $sortDirection = $request->direction ?? 'desc';

        // Validate sort field to prevent SQL injection for popular programmes
        $allowedSortFields = [
            'id', 'name', 'synopsis', 'intensity_level', 'session_type',
            'max_participants', 'duration_minutes', 'total_bookings'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('total_bookings', 'desc');
        }

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Retrieves the list of users that have bookings for a specific schedule.
     *
     * @param  int  $id  The ID of the schedule whose bookings are to be fetched.
     * @param  \Illuminate\Http\Request|null  $request  Optional request with pagination parameters.
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator An array or paginated list of users.
     */
    public static function getUserBookingsByProgrammeId(int $id, $request = null)
    {
        $query = Programme::select(
            'users.id',
            'users.name',
            'users.phone',
            'bookings.status'
        )
            ->join('programme_schedules', 'programme_schedules.programme_id', '=', 'programmes.id')
            ->join('bookings', 'bookings.schedule_id', '=', 'programme_schedules.id')
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->where('programmes.id', $id)
            ->where('programme_schedules.is_cancelled', false)
            ->when($request && $request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'LIKE', "%{$search}%")
                      ->orWhere('users.phone', 'LIKE', "%{$search}%")
                      ->orWhere('bookings.status', 'LIKE', "%{$search}%");
                });
            });

        // Apply sorting if request is provided
        if ($request) {
            $sortField = $request->sort ?? 'users.name';
            $sortDirection = $request->direction ?? 'asc';

            // Validate sort field
            $allowedSortFields = ['users.id', 'users.name', 'users.phone', 'bookings.status'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('users.name', 'asc');
            }
        }

        // Check if pagination parameters are provided
        if ($request && ($request->has('page') || $request->has('limit'))) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Summarize programme data with game type, total active schedules, average bookings, and optional date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getProgrammeRunFrequencies($request)
    {
        $query = Programme::query()
            ->select([
                DB::raw('ANY_VALUE(programmes.id) AS id'),
                DB::raw('ANY_VALUE(programmes.name) AS programme_name'),
                DB::raw('ANY_VALUE(game_types.name) AS game_type_name'),
                DB::raw('ANY_VALUE(programmes.duration_minutes) AS duration_minutes'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT users.name) FROM users WHERE users.id IN (SELECT trainer_id FROM programme_schedules WHERE programme_schedules.programme_id = programmes.id)) AS trainer_names'),
                DB::raw('(SELECT MAX(updated_at) FROM programme_schedules WHERE programme_schedules.programme_id = programmes.id AND programme_schedules.is_cancelled = 1) AS last_cancellation_date')
            ])
            ->join('game_types', 'game_types.id', '=', 'programmes.type_id')
            ->leftJoin('programme_schedules', function($join) use ($request) {
                $user = auth()->user();
                $userRole = $user->role ?? null;
                $join->on('programme_schedules.programme_id', '=', 'programmes.id')
                    ->where('programme_schedules.is_cancelled', false)
                    ->when(!in_array($userRole, ['admin', 'superadmin']), function($query) use($user) {
                        return $query->where('programme_schedules.trainer_id', '=', $user->id);
                    })
                    ->when($request->startDate, function($query, $startDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($startDate)));
                        return $query->where('programme_schedules.day', '>=', $day)
                            ->where('programme_schedules.start_time', '>=', $time);
                    })
                    ->when($request->endDate, function($query, $endDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($endDate)));
                        return $query->where('programme_schedules.day', '<=', $day)
                            ->where('programme_schedules.end_time', '<=', $time);
                    });
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('programmes.name', 'LIKE', "%{$search}%")
                      ->orWhere('game_types.name', 'LIKE', "%{$search}%");
                });
            })
            ->groupBy([
                'programmes.id',
            ]);

        // Apply sorting
        $sortField = $request->sort ?? 'programme_name';
        $sortDirection = $request->direction ?? 'asc';

        // Validate sort field
        $allowedSortFields = ['id', 'programme_name', 'game_type_name', 'duration_minutes'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderByRaw("ANY_VALUE({$sortField}) " . ($sortDirection === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderByRaw('ANY_VALUE(programmes.name) ASC');
        }

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Get programme take up rates with optional date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getProgrammeTakeUpRates($request)
    {
        $query = Programme::query()
            ->select([
                DB::raw('ANY_VALUE(programmes.id) as id'),
                DB::raw('ANY_VALUE(programmes.name) as programme_name'),
                DB::raw('ANY_VALUE(programmes.intensity_level) as intensity_level'),
                DB::raw('ANY_VALUE(programmes.max_participants) as max_participants'),
                DB::raw('ANY_VALUE(game_types.name) as game_type_name'),
                DB::raw('ANY_VALUE(programme_schedules.day) as day'),
                DB::raw('ANY_VALUE(programme_schedules.start_time) as start_time'),
                DB::raw('ANY_VALUE(programmes.duration_minutes) as duration_minutes'),
                // DB::raw('ANY_VALUE((SELECT GROUP_CONCAT(DISTINCT users.name) FROM users WHERE users.id IN (SELECT trainer_id FROM programme_schedules WHERE programme_schedules.programme_id = programmes.id))) as trainer_names'),
                DB::raw('ANY_VALUE(users.name) as trainer_name'),
                DB::raw('ANY_VALUE(COALESCE(booking_stats.bookings_count, 0)) as total_bookings'),
                DB::raw('ANY_VALUE(CASE WHEN programmes.max_participants > 0 THEN ROUND((COALESCE(booking_stats.bookings_count, 0) / programmes.max_participants) * 100, 2) ELSE 0 END) as take_up_rate')
            ])
            ->join('game_types', 'game_types.id', '=', 'programmes.type_id')
            ->join('programme_schedules', function($join) use ($request) {
                $user = auth()->user();
                $userRole = $user->role ?? null;
                $join->on('programme_schedules.programme_id', '=', 'programmes.id')
                    ->where('programme_schedules.is_cancelled', false)
                    ->when(!in_array($userRole, ['admin', 'superadmin']), function($query) use($user) {
                        return $query->where('programme_schedules.trainer_id', '=', $user->id);
                    })
                    ->when($request->startDate, function($query, $startDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($startDate)));
                        return $query->where('programme_schedules.day', '>=', $day)
                            ->where('programme_schedules.start_time', '>=', $time);
                    })
                    ->when($request->endDate, function($query, $endDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($endDate)));
                        return $query->where('programme_schedules.day', '<=', $day)
                            ->where('programme_schedules.end_time', '<=', $time);
                    });
            })
            ->join('users', 'users.id', '=', 'programme_schedules.trainer_id')
            ->leftJoin(DB::raw("(
                SELECT schedule_id, COUNT(*) as bookings_count
                FROM bookings
                WHERE bookings.status IN ('confirmed', 'completed')
                GROUP BY schedule_id
                ) as booking_stats"),
             'booking_stats.schedule_id', '=', 'programme_schedules.id'
            )
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('programmes.name', 'LIKE', "%{$search}%")
                      ->orWhere('game_types.name', 'LIKE', "%{$search}%")
                      ->orWhere('programmes.intensity_level', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->sorts, function ($query, $sorts) {
                $sortArray = explode(',', $sorts);
                foreach ($sortArray as $sort) {
                    [$field, $direction] = explode(':', $sort);
                    $field = \Str::snake($field);
                    $query->orderByRaw("ANY_VALUE({$field}) {$direction}");
                }
            }, function ($query) {
                $query->orderByRaw('ANY_VALUE(programme_schedules.day) ASC')
                    ->orderByRaw('ANY_VALUE(programme_schedules.start_time) ASC');
            })
            ->groupBy([
                'programme_schedules.day',
                'programme_schedules.start_time',
                'users.id'
            ]);

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Get programme cancellation frequencies with optional date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getProgrammeCancellationFrequencies($request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $query = Programme::query()
            ->select([
                'programmes.id',
                'programmes.name AS programme_name',
                'game_types.name AS game_type_name',
                'programmes.duration_minutes',
                'programmes.intensity_level',
                // DB::raw('COALESCE(SUM(cancelled_bookings_count), 0) as total_runs'),
                // DB::raw('(SELECT GROUP_CONCAT(DISTINCT users.name) FROM users WHERE users.id IN (SELECT trainer_id FROM programme_schedules WHERE programme_schedules.programme_id = programmes.id)) AS trainer_names'),
                'users.name AS trainer_name',

                // DB::raw('COUNT(DISTINCT cancelled_schedules.id) as cancellations'),
                DB::raw('CASE WHEN COUNT(DISTINCT programme_schedules.id) > 0 THEN ROUND((COUNT(DISTINCT cancelled_schedules.id) / COUNT(DISTINCT programme_schedules.id)) * 100, 2) ELSE 0 END AS cancellation_rate')
            ])
            ->join('game_types', 'game_types.id', '=', 'programmes.type_id')
            ->join('programme_schedules', function($join) use ($request, $user, $userRole) {
                $join->on('programme_schedules.programme_id', '=', 'programmes.id')
                    ->when(!in_array($userRole, ['admin', 'superadmin']), function($query) use($user) {
                        return $query->where('programme_schedules.trainer_id', '=', $user->id);
                    })
                    ->when($request->startDate, function($query, $startDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($startDate)));
                        return $query->where(function($query) use ($day, $time) {
                            $query->where('programme_schedules.day', '>=', $day)
                                ->where('programme_schedules.start_time', '>=', $time);
                        });
                    })
                    ->when($request->endDate, function($query, $endDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($endDate)));
                        return $query->where(function($query) use ($day, $time) {
                            $query->where('programme_schedules.day', '<=', $day)
                                ->where('programme_schedules.end_time', '<=', $time);
                        });
                    });
            })
            ->join('users', 'users.id', '=', 'programme_schedules.trainer_id')
            // Join only cancelled schedules for cancellations count
            ->join('programme_schedules as cancelled_schedules', function($join) use ($request, $user, $userRole) {
                $join->on('cancelled_schedules.programme_id', '=', 'programmes.id')
                    ->where('cancelled_schedules.is_cancelled', true)
                    ->when(!in_array($userRole, ['admin', 'superadmin']), function($query) use($user) {
                        return $query->where('programme_schedules.trainer_id', '=', $user->id);
                    })
                    ->when($request->startDate, function($query, $startDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($startDate)));
                        return $query->where(function($query) use ($day, $time) {
                            $query->where('programme_schedules.day', '>=', $day)
                                ->where('programme_schedules.start_time', '>=', $time);
                        });
                    })
                    ->when($request->endDate, function($query, $endDate) {
                        [$day, $time] = explode(' ', date('Y-m-d H:i:00', strtotime($endDate)));
                        return $query->where(function($query) use ($day, $time) {
                            $query->where('programme_schedules.day', '<=', $day)
                                ->where('programme_schedules.end_time', '<=', $time);
                        });
                    });
            })
            // Join bookings for cancelled status
            ->leftJoin(DB::raw('(
                SELECT schedule_id, COUNT(*) as cancelled_bookings_count
                FROM bookings
                WHERE status = "cancelled"
                GROUP BY schedule_id
            ) as cancelled_bookings'), 'cancelled_bookings.schedule_id', '=', 'programme_schedules.id')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('programmes.name', 'LIKE', "%{$search}%")
                      ->orWhere('game_types.name', 'LIKE', "%{$search}%")
                      ->orWhere('programmes.intensity_level', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->sorts, function ($query, $sorts) {
                $sortArray = explode(',', $sorts);
                foreach ($sortArray as $sort) {
                    [$field, $direction] = explode(':', $sort);
                    $field = \Str::snake($field);
                    $query->orderByRaw("ANY_VALUE({$field}) {$direction}");
                }
            }, function ($query) {
                 $query->orderBy('programmes.name', 'asc');
            })
            ->groupBy('users.id', 'programmes.id');

        // Check if pagination parameters are provided
        if ($request->has('page') || $request->has('limit')) {
            $limit = $request->limit ?? 15; // Default limit of 15 if not specified
            return $query->paginate($limit);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Store uploaded cover image and return public URL
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public static function uploadCover($file)
    {
        try {
            // Ensure the programme directory exists
            $programmeDir = storage_path('app/public/programme');
            if (!file_exists($programmeDir)) {
                mkdir($programmeDir, 0755, true);
            }

            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $stored = \Storage::disk('public')->putFileAs('programme', $file, $fileName);
            $path = 'public/' . $stored;
            $fullPath = storage_path('app/' . $path);

            if (!$stored || !file_exists($fullPath)) {
                throw new \Exception('Failed to upload file');
            }

            // Convert to public URL
            $publicPath = str_replace('public/', 'storage/', $path);
            $url = asset($publicPath);

            return $url;
        } catch (\Exception $e) {
            \Log::error('PROGRAMME COVER UPLOAD SERVICE ERROR: ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public static function getProgrammeCounters()
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $programmes = Programme::query()
            ->when($userRole === 'trainer', function($query) use($user) {
                return $query->where('created_by', '=', $user->id);
            })
            ->get();
        $activeCount = $programmes->where('is_active', true)->count();
        $inactiveCount = $programmes->where('is_active', false)->count();

        return [
            'active' => $activeCount,
            'inactive' => $inactiveCount,
        ];
    }
}
