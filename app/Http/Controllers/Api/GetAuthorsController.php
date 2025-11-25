<?php

namespace App\Http\Controllers\Api;

use App\Actions\Authors\GetAuthors;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetAuthorsController extends Controller
{
    public function __invoke(Request $request, GetAuthors $getAuthors)
    {
        $result = $getAuthors->execute($request);

        if (! $result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
