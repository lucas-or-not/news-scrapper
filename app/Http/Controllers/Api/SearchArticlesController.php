<?php

namespace App\Http\Controllers\Api;

use App\Actions\Articles\SearchArticles;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SearchArticlesController extends Controller
{
    public function __invoke(Request $request, SearchArticles $searchArticles)
    {
        try {
            $articles = $searchArticles->execute($request);

            return response()->json($articles);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
