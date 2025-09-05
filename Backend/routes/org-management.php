<?php

use App\Http\Controllers\OrgManagement\TeamController;
use App\Http\Controllers\OrgManagement\UserInvitationController;
use Illuminate\Support\Facades\Route;

// Organization Management routes (protected by auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Team management
    Route::apiResource('teams', TeamController::class);
    
    // User invitation management
    Route::prefix('teams')->group(function () {
        Route::post('/invite-existing-user', [UserInvitationController::class, 'inviteExistingUser']);
        Route::post('/create-and-invite-user', [UserInvitationController::class, 'createAndInviteUser']);
        Route::delete('/{team}/remove-user', [UserInvitationController::class, 'removeFromTeam']);
        Route::patch('/{team}/update-user-role', [UserInvitationController::class, 'updateTeamRole']);
    });
});
