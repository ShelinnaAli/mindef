<?php

namespace App\Listeners\Announcement;

use App\Events\Announcement\AnnouncementDeleted;
use App\Models\UserNotification;
use App\Models\User;

class SendAnnouncementDeletedNotification
{
    /**
     * Handle the event.
     */
    public function handle(AnnouncementDeleted $event)
    {
        $announcement = $event->announcement;
        $userIds = User::whereIn('role', ['user', 'trainer'])->pluck('id');
        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'announcement_id' => $announcement->id,
                'type' => 'announcement',
                'title' => 'Announcement Deleted',
                'message' => 'A new announcement has been deleted. "' . $announcement->title . '"' .
                '<br><em>' . $announcement->content . '</em>',
                'is_read' => false,
            ]);
        }
    }
}
