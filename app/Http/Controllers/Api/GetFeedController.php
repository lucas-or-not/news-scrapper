<?php

namespace App\Http\Controllers\Api;

use App\Actions\UserPreferences\GetFeed;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GetFeedController extends Controller
{
    public function __invoke(Request $request, GetFeed $getFeed)
    {
        try {
            $feed = $getFeed->execute($request);

            return response()->json($feed);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve feed',
            ], 500);
        }
    }
}
