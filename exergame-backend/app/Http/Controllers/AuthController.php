<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and their emergency contact.
     * Validates input, creates user, emergency contact, and returns auth token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required|string',
            ]);

            $data = $this->decryptData($request->data);
            Auth\RegisterRequest::validateDecryptedData($data);

            \DB::beginTransaction();
            $user = $this->registerUserData($data);
            if (! $user) {
                throw new \Exception('Failed to create user', 500);
            }

            $this->registerUserEmergencyContact($data, $user->id);

            $userAuth = UserService::generateTokenForUser($user);
            \DB::commit();

            return $this->response(
                'Registration successful',
                $userAuth,
                true
            );
        } catch (ValidationException $e) {
            \DB::rollBack();

            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('REGISTRATION ERROR: ', [$e->getMessage(), $request->all()]);

            return $this->errorResponse('Registration failed :' . $e->getMessage());
        }
    }

    /**
     * Map input data and create a new user record.
     * Handles password hashing and default values.
     *
     * @param  array  $data
     * @return \App\Models\User|null
     */
    private function registerUserData($data)
    {
        // Map camelCase input to snake_case for User model
        $userMappedData = $this->mapInputData($data, User::class);
        // Handle password hashing
        if (isset($userMappedData['password'])) {
            $userMappedData['password'] = bcrypt($userMappedData['password']);
        }

        // Set default values
        $userMappedData['role'] = $userMappedData['role'] ?? 'user';
        $userMappedData['is_active'] = (bool) ($userMappedData['is_active'] ?? true);

        return UserService::createUser($userMappedData);
    }

    /**
     * Map input data and create a new emergency contact for the user.
     *
     * @param  array  $data
     * @param  int  $userId
     * @return \App\Models\UserEmergencyContact|null
     */
    private function registerUserEmergencyContact($data, $userId)
    {
        // Map camelCase input to snake_case for UserEmergencyContact model
        $emergencyMappedData['name'] = $data['emergencyContactName'] ?? "";
        $emergencyMappedData['phone'] = $data['emergencyContactNumber'] ?? "";
        $emergencyMappedData['relationship'] = $data['emergencyRelationship'] ?? "";
        $emergencyMappedData['is_aggreed_consent'] = $data['emergencyIsAggreedConsent'] === 'on' || $data['emergencyIsAggreedConsent'] === true || $data['emergencyIsAggreedConsent'] === 'true';

        return UserService::createUserEmergencyContact($emergencyMappedData, $userId);
    }

    /**
     * Authenticate user and return auth token.
     * Validates input and credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    // public function login(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'data' => 'required|string',
    //         ]);

    //         $data = $this->decryptData($request->data);

    //         Auth\LoginRequest::validateDecryptedData($data);

    //         $user = UserService::getUserByUsername($data['username']);
    //         if (! $user) {
    //             throw new \Exception('invalid user', 401);
    //         }

    //         if (! auth()->attempt($data)) {
    //             throw new \Exception('Invalid password', 401);
    //         }
    //         $userAuth = UserService::generateTokenForUser(auth()->user());

    //         return $this->response(
    //             'Login successful',
    //             $userAuth,
    //             true
    //         );
    //     } catch (ValidationException $e) {
    //         return $this->validationErrorResponse($e->errors(), 422);
    //     } catch (\Exception $e) {
    //         \Log::error('LOGIN ERROR: ', [$e->getMessage(), $request->all()]);

    //         return $this->errorResponse($e->getMessage());
    //     }
    // }

    public function login(Request $request)
    {
        try {
            // 1. Ambil data mentah (karena Nuxt mengirim JSON plain)
            $credentials = $request->only(['username', 'password']);

            // 2. Validasi menggunakan static method di LoginRequest (sesuai pola kamu)
            Auth\LoginRequest::validateDecryptedData($credentials);

            // 3. Cari User
            $user = UserService::getUserByUsername($credentials['username']);
            if (!$user) {
                return $this->errorResponse('User not found', 401);
            }

            // 4. Attempt Login
            if (!auth()->attempt($credentials)) {
                return $this->errorResponse('Invalid password', 401);
            }

            $userAuth = UserService::generateTokenForUser(auth()->user());

            // 5. Kembalikan response (withEncryption: false)
            return $this->response('Login successful', $userAuth, false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('LOGIN ERROR: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Logout the authenticated user by revoking all tokens.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                $user->tokens()->delete();

                return $this->response('Logout successful');
            }
            throw new \Exception('User not authenticated', 401);
        } catch (\Exception $e) {
            \Log::error('LOGOUT ERROR: ', [$e->getMessage(), $request->all()]);

            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Change the password for the authenticated user.
     * Validates input and current password, updates password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required|string',
            ]);

            $data = $this->decryptData($request->data);
            Auth\ChangePasswordRequest::validateDecryptedData($data);

            $data['current_password'] = $data['currentPassword'];
            $data['new_password'] = $data['newPassword'];
            $data['confirm_password'] = $data['confirmPassword'];
            unset($data['currentPassword'], $data['newPassword'], $data['confirmPassword']);

            $user = $request->user();

            if (! $user) {
                throw new \Exception('User not authenticated', 401);
            }

            // Check if current password is correct
            if (! \Hash::check(
                $data['current_password'],
                $user->getRawOriginal('password')
            )) {
                throw new \Exception('Current password is incorrect', 400);
            }

            $user->update([
                'password' => \Hash::make($data['new_password']),
            ]);

            // Optionally, revoke all existing tokens to force re-login
            // $user->tokens()->delete();

            return $this->response('Password changed successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('CHANGE PASSWORD ERROR: ', [$e->getMessage(), $request->all()]);

            return $this->errorResponse('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Validate username and generate recommendations if needed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUsername(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|min:1',
            ]);

            $data = UserService::validateAndGenerateUsernameRecommendations($request->username);

            return $this->response(
                $data['message'],
                $data,
            );
        } catch (\Exception $e) {
            \Log::error('VALIDATE USERNAME ERROR: ', [$e->getMessage(), $request->all()]);
            return $this->errorResponse('Failed to validate username: ' . $e->getMessage());
        }
    }
}
