<?php

namespace App\Listeners\Announcement;

use App\Events\Announcement\AnnouncementUpdated;
use App\Models\UserNotification;
use App\Models\User;

class SendAnnouncementUpdatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(AnnouncementUpdated $event)
    {
        $announcement = $event->announcement;
        $userIds = User::whereIn('role', ['user', 'trainer'])->pluck('id');
        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'announcement_id' => $announcement->id,
                'type' => 'announcement',
                'title' => 'Announcement Updated',
                'message' => 'A new announcement has been updated. "' . $announcement->title . '"' .
                '<br><em>' . $announcement->content . '</em>',
                'is_read' => false,
            ]);
        }
    }
}
