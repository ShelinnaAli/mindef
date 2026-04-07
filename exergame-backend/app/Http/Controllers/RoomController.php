<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomService;

class RoomController extends Controller
{
    /**
     * Get all rooms
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll()
    {
        try {
            $rooms = RoomService::getRooms();

            return $this->response('Rooms retrieved successfully', $rooms->toArray());
        } catch (\Exception $e) {
            \Log::error('ROOMS FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch rooms: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get a specific room by ID
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find($id)
    {
        try {
            $room = RoomService::getRoomById($id);

            return $this->response('Room retrieved successfully', $room->toArray());
        } catch (\Exception $e) {
            \Log::error('ROOM FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse('Failed to fetch room: '.$e->getMessage(), 500);
        }
    }
}
