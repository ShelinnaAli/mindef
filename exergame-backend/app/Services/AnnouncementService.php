<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Events\Announcement\AnnouncementDeleted;
use App\Events\Announcement\AnnouncementUpdated;
use App\Events\Announcement\AnnouncementCreated;

class AnnouncementService
{
    public static function getAnnouncements($request)
    {
        // Optionally handle filters, expand, etc.
        $query = Announcement::with('type')
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('is_active', $request->get('status') === 'active');
            })
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->get('search');
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('content', 'LIKE', "%{$search}%")
                          ->orWhereHas('type', function ($typeQuery) use ($search) {
                              $typeQuery->where('name', 'LIKE', "%{$search}%");
                          });
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

        // Handle pagination if page parameter is provided
        if ($request->has('page')) {
            $limit = $request->get('limit', 10); // Default limit of 10
            return $query->paginate($limit);
        }

        return $query->get();
    }

    public static function getAnnouncementTypes()
    {
        return AnnouncementType::all();
    }

    public static function getAnnouncementById($id)
    {
        return Announcement::with('type')->findOrFail($id);
    }

    public static function createAnnouncement(array $data)
    {
        $announcement = Announcement::create($data);
        event(new AnnouncementCreated($announcement));
        return $announcement;
    }

    public static function updateAnnouncement($id, array $data)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update($data);
        event(new AnnouncementUpdated($announcement));
        return $announcement;
    }

    public static function deleteAnnouncement($id)
    {
        $announcement = Announcement::findOrFail($id);
        // Soft delete: set is_active to false
        $announcement->is_active = false;
        $announcement->save();
        event(new AnnouncementDeleted($announcement));
    }
}
