<?php

namespace App\Http\Controllers;

use App\Services\GameTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameTypeController extends Controller
{
    /**
     * Get all active game types
     */
    public function findAll(Request $request): JsonResponse
    {
        try {
            $gameTypes = GameTypeService::getAllActiveGameTypes();

            return $this->response(
                'Game types retrieved successfully',
                $gameTypes->toArray()
            );
        } catch (\Exception $e) {
            Log::error('GAME TYPES FETCH ERROR: ', [$e->getMessage()]);

            return $this->errorResponse(
                'Failed to fetch game types: '.$e->getMessage(),
                500
            );
        }
    }
}
