<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\OtpController;

Route::post('/register', [RegisterController::class, '__invoke']);
Route::post('/login', [LoginController::class, '__invoke']);
Route::post('/verify-otp', [OtpController::class, 'verify']);
