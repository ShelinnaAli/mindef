<?php

namespace App\Services;

use App\Models\GameType;
use Illuminate\Database\Eloquent\Collection;

class GameTypeService
{
    /**
     * Get all active game types
     */
    public static function getAllActiveGameTypes(): Collection
    {
        return GameType::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
