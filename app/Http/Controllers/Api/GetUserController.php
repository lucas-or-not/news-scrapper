<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\GetUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetUserController extends Controller
{
    public function __invoke(Request $request, GetUser $getUser)
    {
        $result = $getUser->execute($request);

        if (! $result['success']) {
            $statusCode = $result['error'] === 'User not authenticated' ? 401 : 500;

            return response()->json($result, $statusCode);
        }

        return response()->json($result);
    }
}
