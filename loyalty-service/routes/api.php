<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/users/{user}/achievements', [UserController::class, 'achievements']);
Route::get('/admin/users/achievements', [AdminController::class, 'allAchievements']);
