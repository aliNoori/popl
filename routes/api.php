<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerificationController;

Route::prefix('auth')->group(function () {
    Route::post('/send-verification-code', [VerificationController::class, 'send']);
    Route::post('/verify-code', [VerificationController::class, 'check']);
    Route::middleware('auth:sanctum')->post('/update-profile', [AuthController::class, 'updateProfile']);

});
