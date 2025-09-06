<?php

use App\Http\Controllers\OrgManagement\BudgetController;
use App\Http\Controllers\OrgManagement\LeadOnboardingController;
use App\Http\Controllers\OrgManagement\ReceiptController;
use App\Http\Controllers\OrgManagement\TeamController;
use App\Http\Controllers\OrgManagement\TransactionController;
use Illuminate\Support\Facades\Route;

// Organization Management routes (protected by auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Team management
    Route::apiResource('teams', TeamController::class);
    
    // Lead onboarding
    Route::post('leads/invite', [LeadOnboardingController::class, 'inviteTeamLead']);
    
    // Budget management
    Route::prefix('teams/{team}')->group(function () {
        Route::apiResource('budgets', BudgetController::class);
        Route::patch('budgets/{budget}/toggle-status', [BudgetController::class, 'toggleStatus']);
        Route::get('budgets/{budget}/summary', [BudgetController::class, 'summary']);
    });
    
    // Transaction and Receipt management (scoped to budget)
    Route::prefix('budgets/{budget}')->group(function () {
        // Transaction management
        Route::apiResource('transactions', TransactionController::class);
        
        // Receipt management
        Route::apiResource('receipts', ReceiptController::class)->except(['store', 'update']);
        Route::post('receipts/upload', [ReceiptController::class, 'upload']);
        Route::put('receipts/{receipt}/replace', [ReceiptController::class, 'replace']);
        Route::get('receipts/{receipt}/url', [ReceiptController::class, 'url']);
    });
});
