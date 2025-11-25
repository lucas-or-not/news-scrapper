<?php

use App\Http\Controllers\Api\GetAuthorsController;
use App\Http\Controllers\Api\GetCategoriesController;
use App\Http\Controllers\Api\GetFeedController;
use App\Http\Controllers\Api\GetPreferencesController;
use App\Http\Controllers\Api\GetSourcesController;
use App\Http\Controllers\Api\GetUserController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SearchArticlesController;
use App\Http\Controllers\Api\ShowArticleController;
use App\Http\Controllers\Api\UpdatePreferencesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

Route::get('/sources', GetSourcesController::class);
Route::get('/categories', GetCategoriesController::class);
Route::get('/authors', GetAuthorsController::class);

// Public article routes - accessible to guests
Route::get('/articles/search', SearchArticlesController::class);
Route::get('/articles/{article}', ShowArticleController::class)->whereNumber('article');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::get('/user', GetUserController::class);

    // User preferences
    Route::get('/user/preferences', GetPreferencesController::class);
    Route::put('/user/preferences', UpdatePreferencesController::class);
    Route::get('/user/feed', GetFeedController::class);
});
