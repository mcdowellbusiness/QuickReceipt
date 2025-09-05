<?php

use App\Http\Controllers\Admin\OrganizationOnboardingController;
use Illuminate\Support\Facades\Route;

// Admin routes (will add super admin middleware later)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/onboard-organization', [OrganizationOnboardingController::class, 'createUserAndOrganization']);
});
