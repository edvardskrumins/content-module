<?php

use App\Http\Controllers\ContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('content-module')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'module' => 'content-module',
            'message' => 'Content module routes are loaded successfully!',
        ]);
    });

    Route::apiResource('contents', ContentController::class);
});
