<?php

namespace App\Http\Controllers\Api;

use App\Actions\Categories\GetCategories;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetCategoriesController extends Controller
{
    public function __invoke(Request $request, GetCategories $getCategories)
    {
        $result = $getCategories->execute($request);

        if (! $result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
