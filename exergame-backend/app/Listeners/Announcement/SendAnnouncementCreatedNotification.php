<?php

namespace App\Listeners\Announcement;

use App\Events\Announcement\AnnouncementCreated;
use App\Models\UserNotification;
use App\Models\User;

class SendAnnouncementCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(AnnouncementCreated $event)
    {
        $announcement = $event->announcement;
        $userIds = User::whereIn('role', ['user', 'trainer'])->pluck('id');
        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'announcement_id' => $announcement->id,
                'type' => 'announcement',
                'title' => 'New Announcement',
                'message' => 'A new announcement has been posted. "' . $announcement->title . '"' .
                '<br><em>' . $announcement->content . '</em>',
                'is_read' => false,
            ]);
        }
    }
}
