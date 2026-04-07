<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class RoomService
{
    /**
     * Get all rooms
     */
    public static function getRooms(): Collection
    {
        return Room::select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get room by ID
     */
    public static function getRoomById(int $id): ?Room
    {
        return Room::findOrFail($id);
    }
}
