<?php

use App\Http\Controllers\OrgManagement\BudgetController;
use App\Http\Controllers\OrgManagement\TeamController;
use App\Http\Controllers\OrgManagement\TransactionController;
use Illuminate\Support\Facades\Route;

// Organization Management routes (protected by auth:sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Team management
    Route::apiResource('teams', TeamController::class);
    
    // Budget management
    Route::prefix('teams/{team}')->group(function () {
        Route::apiResource('budgets', BudgetController::class);
        Route::patch('budgets/{budget}/toggle-status', [BudgetController::class, 'toggleStatus']);
        Route::get('budgets/{budget}/summary', [BudgetController::class, 'summary']);
        
        // Transaction management
        Route::apiResource('transactions', TransactionController::class);
    });
});
