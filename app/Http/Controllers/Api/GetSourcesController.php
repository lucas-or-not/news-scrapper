<?php

namespace App\Http\Controllers\Api;

use App\Actions\Sources\GetSources;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetSourcesController extends Controller
{
    public function __invoke(Request $request, GetSources $getSources)
    {
        $result = $getSources->execute($request);

        if (! $result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
