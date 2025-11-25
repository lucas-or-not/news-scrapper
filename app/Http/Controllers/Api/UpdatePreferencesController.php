<?php

namespace App\Http\Controllers\Api;

use App\Actions\UserPreferences\UpdatePreferences;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UpdatePreferencesController extends Controller
{
    public function __invoke(Request $request, UpdatePreferences $updatePreferences)
    {
        try {
            $preferences = $updatePreferences->execute($request);

            return response()->json($preferences);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update preferences',
            ], 500);
        }
    }
}
