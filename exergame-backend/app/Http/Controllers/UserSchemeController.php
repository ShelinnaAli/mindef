<?php

namespace App\Http\Controllers;

use App\Models\UserScheme;

class UserSchemeController extends Controller
{
    /**
     * Get all active user schemes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAll()
    {
        try {
            $schemes = UserScheme::where('is_active', true)
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $schemes,
            ]);
        } catch (\Exception $e) {
            \Log::error('USER SCHEME FETCH ERROR: ', [$e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user schemes: '.$e->getMessage(),
            ], 500);
        }
    }
}
