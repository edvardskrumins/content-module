<?php

use Illuminate\Support\Facades\Route;


Route::prefix('content')->group(function () {
    Route::get('/health', function () {
      return response()->json([
          'module' => 'content-module',
          'message' => 'Content module routes are loaded successfully!',
      ]);
    });
});
