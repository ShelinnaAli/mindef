<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Get total registered users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalUsers(Request $request)
    {
        try {
            $users = UserService::getUsers($request);
            $count = $users->count();

            return $this->response('Total users retrieved successfully', ['total' => $count]);
        } catch (\Exception $e) {
            \Log::error('GET TOTAL USERS ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to retrieve total users: '.$e->getMessage());
        }
    }

    /**
     * Retrieve all users with optional filters from the request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll(Request $request)
    {
        try {
            $users = UserService::getUsers($request);

            return $this->response(
                'Users retrieved successfully',
                $users->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('GET USERS ERROR: ', [$e->getMessage(), $request->all()]);

            return $this->errorResponse('Failed to retrieve users: '.$e->getMessage());
        }
    }

    /**
     * Retrieve a specific user by ID or the current authenticated user.
     * Optionally loads related models if 'expand' is provided in the request.
     *
     * @param  int|null  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request, $id = null)
    {
        try {
            $user = $id ? UserService::getUserById($id) : $request->user();
            if ($request->get('expand')) {
                $user = $this->loadRelationModel($user, $request->get('expand'));
            }

            return $this->response(
                'User retrieved successfully',
                $user->toArray(),
                true
            );
        } catch (\Exception $e) {
            \Log::error('GET USER ERROR: ', [$e->getMessage(), $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to retrieve user: '.$e->getMessage());
        }
    }

    /**
     * Create a new user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = [];
        $mappedData = [];

        try {
            $data = is_string($request->input('data'))
                ? $this->decryptData($request->input('data'))
                : $request->except('data');

            AddUserRequest::validateDecryptedData($data);

            DB::beginTransaction();

            // Map camelCase input to snake_case for database
            $mappedData = $this->mapInputData($data, User::class);

            $user = UserService::createUser($mappedData);

            // Map camelCase input to snake_case for UserEmergencyContact model
            $emergencyMappedData['name'] = $data['emergencyContactName'] ?? "";
            $emergencyMappedData['phone'] = $data['emergencyContactNumber'] ?? "";
            $emergencyMappedData['relationship'] = $data['emergencyRelationship'] ?? "";

            $addedEmergencyContact = UserService::updateUserEmergencyContact($emergencyMappedData, $user->id);

            DB::commit();
            if ($addedEmergencyContact) {
                $user->load('emergencyContact');
            }

            return $this->response(
                'User created successfully',
                $user->toArray(),
                true
            );
        } catch (ValidationException $e) {
            DB::rollBack();

            return $this->validationErrorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('USER CREATE ERROR: ', [$e->getMessage(), $data, $mappedData, $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to create user: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update user profile or admin update for a specific user.
     * Handles both profile update (current user) and admin update (by ID).
     * Also updates emergency contact for admin update.
     *
     * @param  int|null  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id = null)
    {
        try {
            $request->validate([
                'data' => 'required|string',
            ]);

            $data = $this->decryptData($request->data, ['id' => $id]);

            if ($id === null) {
                // Profile update - update current user's profile (only name and phone)
                $user = $request->user();

                UpdateRequest::validateDecryptedData($data);

                // Map camelCase input to snake_case for User model
                $userMappedData = $this->mapInputData($data, User::class);

                $updatedUser = UserService::updateUser($user->id, $userMappedData);

                return $this->response(
                    'Profile updated successfully',
                    $updatedUser->only('id', 'name', 'username', 'phone', 'role'),
                    true
                );
            } else {
                // User update - admin updating a specific user (all fields)
                UpdateUserRequest::validateDecryptedData($data, $id);

                DB::beginTransaction();

                // Map camelCase input to snake_case for User model
                $userMappedData = $this->mapInputData($data, User::class);
                unset($userMappedData['username']); // Prevent username updates
                $updatedUser = UserService::updateUser($id, $userMappedData);

                // Map camelCase input to snake_case for UserEmergencyContact model
                $emergencyMappedData['name'] = $data['emergencyContactName'] ?? "";
                $emergencyMappedData['phone'] = $data['emergencyContactNumber'] ?? "";
                $emergencyMappedData['relationship'] = $data['emergencyRelationship'] ?? "";

                $updatedEmergencyContact = UserService::updateUserEmergencyContact($emergencyMappedData, $id);

                DB::commit();
                if ($updatedEmergencyContact) {
                    $updatedUser->load('emergencyContact');
                }

                return $this->response(
                    'User updated successfully',
                    $updatedUser->toArray(),
                    true
                );
            }
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('UPDATE USER ERROR: ', [$e->getMessage(), $request->all(), $e->getTraceAsString()]);

            return $this->errorResponse('Failed to update user: '.$e->getMessage());
        }
    }

    /**
     * Delete a user and their emergency contact by user ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Delete emergency contact first
            UserService::deleteUserEmergencyContact($id);

            // Delete the user
            $deleted = UserService::deleteUser($id);

            if (! $deleted) {
                throw new \Exception('Failed to delete user');
            }

            DB::commit();

            return $this->response(
                'User deleted successfully',
                ['deleted_user_id' => $id],
                true
            );
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('DELETE USER ERROR: ', [$e->getMessage(), $request->all()]);

            return $this->errorResponse('Failed to delete user: '.$e->getMessage());
        }
    }
}
