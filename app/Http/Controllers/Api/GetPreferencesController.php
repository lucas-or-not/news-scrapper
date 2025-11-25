<?php

namespace App\Http\Controllers\Api;

use App\Actions\UserPreferences\GetPreferences;
use App\Http\Controllers\Controller;
use Exception;

class GetPreferencesController extends Controller
{
    public function __invoke(GetPreferences $getPreferences)
    {
        try {
            $preferences = $getPreferences->execute();

            return response()->json($preferences);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve preferences',
            ], 500);
        }
    }
}
