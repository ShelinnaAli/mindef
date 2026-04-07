<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEmergencyContact;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Generate an access token for the given user, revoke old tokens, and update last login.
     */
    public static function generateTokenForUser(User $user): array
    {
        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Revoke existing tokens (optional - for security)
        $user->tokens()->delete();

        // Create new token
        $tokenResult = $user->createToken(env('APP_NAME'), ['*']);
        $token = $tokenResult->accessToken;

        if ($tokenResult->token) {
            $tokenResult->token->expires_at = now()->addWeeks(2);
            $tokenResult->token->save();
        }

        return [
            'user' => $user->only('id', 'name', 'username', 'role'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addWeeks(2)->toDateTimeString(),
        ];
    }

    /**
     * Create or update a user record based on username.
     */
    public static function createUser(array $data): User
    {
        return User::updateOrCreate(
            ['username' => $data['username']],
            $data
        );
    }

    /**
     * Update user data for the given user ID.
     * Excludes 'id' from update fields.
     */
    public static function updateUser(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $user->update($data);

        return $user;
    }

    /**
     * Create or update a user's emergency contact record.
     *
     * @param  int  $userId
     */
    public static function createUserEmergencyContact(array $data, $userId): UserEmergencyContact
    {
        $data['user_id'] = $userId;

        return UserEmergencyContact::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    /**
     * Update a user's emergency contact record.
     */
    public static function updateUserEmergencyContact(array $data, int $userId): UserEmergencyContact
    {
        $data['user_id'] = $userId;

        return UserEmergencyContact::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    /**
     * Retrieve a user by their ID.
     */
    public static function getUserById(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Retrieve a user by their username.
     */
    public static function getUserByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    /**
     * Retrieve all users with pagination support, optionally loading relations if 'expand' is provided in the request.
     *
     * Supported query parameters:
     * - page: Page number for pagination
     * - limit: Items per page (1-100, default: 15)
     * - expand: Comma-separated list of relations to load
     * - role: Filter by user role
     * - search: Search in name, username, or phone fields
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public static function getUsers($request): LengthAwarePaginator|Collection
    {
        $query = User::query()
            ->where('role', '!=', 'superadmin') // Exclude super superadmin
            ->when($request->get('expand'), function ($query, $expand) {
                return $query->with(explode(',', $expand));
            })
            ->when($request->get('role'), function ($query, $role) {
                return $query->where('role', $role);
            })
            ->when($request->get('search'), function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
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
        $page = $request->get('page');
        $limit = $request->get('limit', 15); // Default limit to 15 if not specified

        if ($page || $request->has('limit')) {
            // Validate limit to prevent excessive requests
            $limit = min(max((int) $limit, 1), 100); // Between 1 and 100

            // Return paginated results
            return $query->paginate($limit, ['*'], 'page', $page);
        }

        // Return all results if no pagination parameters
        return $query->get();
    }

    /**
     * Get the total number of users.
     */
    public static function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Delete a user by their ID.
     */
    public static function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);

        return $user->delete();
    }

    /**
     * Delete a user's emergency contact by user ID.
     */
    public static function deleteUserEmergencyContact(int $userId): bool
    {
        return UserEmergencyContact::where('user_id', $userId)->delete();
    }

    /**
     * Validate username format and availability, and generate recommendations if needed.
     */
    public static function validateAndGenerateUsernameRecommendations(string $username): array
    {
        $isValidFormat = preg_match('/^[a-zA-Z0-9]+$/', $username);
        $isAvailable = ! User::where('username', $username)->exists();

        $response = [
            'isValid' => $isValidFormat && $isAvailable,
            'isValidFormat' => $isValidFormat,
            'isAvailable' => $isAvailable,
            'recommendations' => [],
        ];

        if (! $isValidFormat) {
            $response['message'] = 'Username must contain only letters and numbers';
            $response['recommendations'] = self::generateUsernameRecommendations($username);
        } elseif (! $isAvailable) {
            $response['message'] = 'Username is already taken';
            $response['recommendations'] = self::generateUsernameRecommendations($username);
        } else {
            $response['message'] = 'Username is available';
        }

        return $response;
    }

    /**
     * Generate recommended usernames based on the original input.
     *
     * @param  string  $originalUsername
     * @return array
     */
    private static function generateUsernameRecommendations($originalUsername)
    {
        // Clean the username to alphanumeric only
        $cleanUsername = preg_replace('/[^a-zA-Z0-9]/', '', $originalUsername);

        // If clean username is too short, pad with suggested text
        if (strlen($cleanUsername) < 3) {
            $cleanUsername = $cleanUsername.'user';
        }

        $recommendations = [];
        $baseUsername = strtolower($cleanUsername);

        // Generate different variations
        $variations = [
            $baseUsername,
            $baseUsername.date('y'), // Add current year (2-digit)
            $baseUsername.rand(10, 99), // Add random 2-digit number
            $baseUsername.rand(100, 999), // Add random 3-digit number
            $baseUsername.date('Y'), // Add current year (4-digit)
            'user'.$baseUsername,
            $baseUsername.'user',
        ];

        // Check each variation and add to recommendations if available
        foreach ($variations as $variation) {
            if (strlen($variation) >= 6 && strlen($variation) <= 20) { // Reasonable length constraints
                if (! User::where('username', $variation)->exists()) {
                    $recommendations[] = $variation;

                    // Limit to 5 recommendations
                    if (count($recommendations) >= 5) {
                        break;
                    }
                }
            }
        }

        // If we still don't have enough recommendations, generate more with incremental numbers
        if (count($recommendations) < 3) {
            for ($i = 1; $i <= 20; $i++) {
                $variation = $baseUsername.$i;
                if (! User::where('username', $variation)->exists()) {
                    $recommendations[] = $variation;
                    if (count($recommendations) >= 5) {
                        break;
                    }
                }
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Get user list report with optional registration date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public static function getUserListReport($request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $query = User::select([
                DB::raw('ANY_VALUE(users.created_at) as registration_date'),
                DB::raw('ANY_VALUE(user_schemes.name) as scheme_name'),
                DB::raw('ANY_VALUE(users.name) as name'),
                DB::raw('ANY_VALUE(users.phone) as phone'),
                DB::raw('YEAR(CURDATE()) - users.birth_year as age'),
                DB::raw('ANY_VALUE(user_emergency_contacts.name) as emergency_contact_name'),
                DB::raw('ANY_VALUE(user_emergency_contacts.relationship) as emergency_contact_relationship'),
                DB::raw('ANY_VALUE(user_emergency_contacts.phone) as emergency_contact_phone')
            ])
            ->join('user_schemes', 'user_schemes.id', '=', 'users.scheme_id')
            ->leftJoin(
                // Join only the last emergency contact per user
                DB::raw('(
                    SELECT * FROM user_emergency_contacts WHERE id IN (
                        SELECT MAX(id) FROM user_emergency_contacts GROUP BY user_id
                    )
                ) as user_emergency_contacts'),
                'user_emergency_contacts.user_id', '=', 'users.id'
            )
            ->leftJoin('bookings', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('programme_schedules', 'programme_schedules.id', '=', 'bookings.schedule_id')
            ->where('users.role', 'user')
            ->when(!in_array($userRole, ['admin', 'superadmin']), function($query) use($user) {
                return $query->where('programme_schedules.trainer_id', '=', $user->id);
            })
            ->when($request->startDate, function($query, $startDate) {
                $startDate = date('Y-m-d H:i:00', strtotime($startDate));
                return $query->where('users.created_at', '>=', $startDate);
            })
            ->when($request->endDate, function($query, $endDate) {
                $endDate = date('Y-m-d H:i:00', strtotime($endDate));
                return $query->where('users.created_at', '<=', $endDate);
            })
            ->groupBy('users.id')
            ->orderBy('users.created_at')
            ->get();

        return $query;
    }

    /**
     * Get user participation history with optional programme_schedules date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public static function getUserParticipationHistory($request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $query = User::select([
                'user_schemes.name as scheme_name',
                'users.name as user_name',
                DB::raw('YEAR(CURDATE()) - users.birth_year as user_age'),
                'programmes.name as programme_name',
                'game_types.name as game_type_name',
                'programmes.intensity_level',
                'programme_schedules.day',
                'programme_schedules.start_time',
                DB::raw("CASE WHEN bookings.status = 'completed' THEN 'Present' ELSE 'Absent' END as attendance_status"),
            ])
            ->join('user_schemes', 'user_schemes.id', '=', 'users.scheme_id')
            ->join('bookings', 'bookings.user_id', '=', 'users.id')
            ->join('programme_schedules', 'programme_schedules.id', '=', 'bookings.schedule_id')
            ->join('programmes', 'programmes.id', '=', 'programme_schedules.programme_id')
            ->join('game_types', 'game_types.id', '=', 'programmes.type_id')
            ->where('users.role', 'user')
            ->when($userRole === 'trainer', function($query) use($user) {
                return $query->where('programme_schedules.trainer_id', '=', $user->id);
            })
            ->when($userRole === 'user', function($query) use($user) {
                return $query->where('users.id', '=', $user->id);
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
            })
            ->orderBy('users.name')
            ->orderBy('programme_schedules.day')
            ->orderBy('programme_schedules.start_time')
            ->get();

        return $query;
    }
    /**
     * Get user age distributions with optional created_at date range filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public static function getUserAgeDistributions($request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $query = User::query()
            ->select([
                // Age group buckets
                DB::raw("CASE
                    WHEN YEAR(CURDATE()) - birth_year BETWEEN 18 AND 24 THEN '18-24'
                    WHEN YEAR(CURDATE()) - birth_year BETWEEN 25 AND 34 THEN '25-34'
                    WHEN YEAR(CURDATE()) - birth_year BETWEEN 35 AND 44 THEN '35-44'
                    WHEN YEAR(CURDATE()) - birth_year BETWEEN 45 AND 54 THEN '45-54'
                    WHEN YEAR(CURDATE()) - birth_year >= 55 THEN '55+'
                    ELSE 'Unknown'
                END as age_group"),
                DB::raw('COUNT(*) as total'),
            ])
            ->join('bookings', 'bookings.user_id', '=', 'users.id')
            ->join('programme_schedules', 'programme_schedules.id', '=', 'bookings.schedule_id')
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
            })
            ->groupBy('age_group');

        $ageGroups = $query->get();
        $totalUsers = $ageGroups->sum('total');

        // Add percentage for each group
        $result = $ageGroups->map(function($row) use ($totalUsers) {
            $row->percentage = $totalUsers > 0 ? round(($row->total / $totalUsers) * 100, 2) : 0;
            return $row;
        });

        return $result;
    }

    /**
     * Get user participation report with programme and booking details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public static function getUserParticipationReport($request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? null;
        $query = User::select([
                'programme_schedules.day',
                'programme_schedules.start_time',
                'programmes.name as programme_name',
                'programmes.intensity_level',
                'bookings.status',
                DB::raw('YEAR(CURDATE()) - users.birth_year as age'),
                'users.id as user_id',
                'users.name as user_name'
            ])
            ->join('bookings', 'bookings.user_id', '=', 'users.id')
            ->join('programme_schedules', 'programme_schedules.id', '=', 'bookings.schedule_id')
            ->join('programmes', 'programmes.id', '=', 'programme_schedules.programme_id')
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
            })
            ->orderBy('programme_schedules.day')
            ->orderBy('programme_schedules.start_time')
            ->get();

        return $query;
    }
}
