<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Middleware\EmailVerfied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register',[AuthController::class,'register']); 
Route::post('/verfiy_code',[AuthController::class,'verfiy_code']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/stats',[AuthController::class,'stats']);
Route::middleware(['auth:sanctum',EmailVerfied::class])->group(function () {
    Route::apiResource('posts', PostController::class);
});