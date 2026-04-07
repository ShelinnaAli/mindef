<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Find notifications by user id.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByUser(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $notifications = NotificationService::getNotifications($userId);

            return $this->response(
                'Notifications retrieved successfully',
                $notifications->toArray()
            );
        } catch (\Exception $e) {
            Log::error('GAME TYPES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse(
                'Failed to fetch notifications: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Mark a notification as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, int $notificationId)
    {
        try {
            NotificationService::markAsRead($notificationId);

            return $this->response('Notification marked as read successfully');
        } catch (\Exception $e) {
            Log::error('MARK NOTIFICATION AS READ ERROR: ', [$e->getMessage()]);

            return $this->errorResponse(
                'Failed to mark notification as read: '.$e->getMessage(),
                500
            );
        }
    }
}
