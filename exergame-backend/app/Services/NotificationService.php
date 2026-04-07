<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService
{
    /**
     * Get all notifications filtered by user_id.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNotifications(int $userId)
    {
        return UserNotification::where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead(int $notificationId): bool
    {
        $notification = UserNotification::findOrFail($notificationId);
        $notification->is_read = true;

        return $notification->save();
    }
}
