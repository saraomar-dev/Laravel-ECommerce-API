<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
/*auth routes*/
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

/*user mangment routes*/
Route::apiResource('users',UserController::class)->middleware(['auth:sanctum', 'isAdmin']);


/*profile routes*/
Route::apiResource('profiles',ProfileController::class)->middleware('auth:sanctum');
Route::get('/showProfile',[ProfileController::class,'showProfile'])->middleware('auth:sanctum');
Route::post('addImage',[ProfileController::class,'addImage'])->middleware('auth:sanctum');
Route::post('addPhone',[ProfileController::class,'addphone'])->middleware('auth:sanctum');
Route::patch('/editProfile',[ProfileController::class,'editProfile'])->middleware('auth:sanctum');
Route::post('/addAddress',[ProfileController::class,'addAdress'])->middleware('auth:sanctum');
