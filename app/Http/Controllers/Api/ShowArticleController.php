<?php

namespace App\Http\Controllers\Api;

use App\Actions\Articles\ShowArticle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowArticleController extends Controller
{
    public function __invoke(Request $request, ShowArticle $showArticle, $id)
    {
        $result = $showArticle->execute($request, $id);

        if (! $result['success']) {
            $statusCode = $result['error'] === 'Article not found' ? 404 : 500;

            return response()->json($result, $statusCode);
        }

        return response()->json($result);
    }
}
