<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__.'/auth.php';

// Include admin routes
require __DIR__.'/admin.php';

// Include organization management routes
require __DIR__.'/org-management.php';

// Protected routes
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});