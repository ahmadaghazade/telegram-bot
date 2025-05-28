<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/posts', [PostController::class, 'index'])->middleware('auth:sanctum');
Route::get('/posts/{post}', [PostController::class, 'show'])->middleware('auth:sanctum');
Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:sanctum');
