<?php

use App\Http\Controllers\FileController;
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

// File management routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::put('/files/{fileId}/replace', [FileController::class, 'replace']);
    Route::delete('/files/{fileId}', [FileController::class, 'delete']);
    Route::get('/files/{fileId}/url', [FileController::class, 'getUrl']);
    Route::get('/files/{fileId}', [FileController::class, 'show']);
});