<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LogoutUser;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutUser $logoutUser)
    {
        try {
            $result = $logoutUser->execute($request);

            if (! $result['success']) {
                return response()->json($result, 401);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
