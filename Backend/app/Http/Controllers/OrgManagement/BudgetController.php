<?php

namespace App\Http\Controllers\OrgManagement;

use App\Exceptions\TeamException;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Team;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * List all budgets for a team
     */
    public function index(Team $team)
    {
        try {
            $user = Auth::user();
            
            $budgets = $this->budgetService->getTeamBudgets($user, $team);

            return response()->json($budgets);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Show a specific budget
     */
    public function show(Team $team, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            $budget = $this->budgetService->getBudget($user, $team, $budget);

            return response()->json($budget);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Create a new budget
     */
    public function store(Request $request, Team $team)
    {
        try {
            $user = Auth::user();
            
            // Validate the request data
            $validatedData = $this->budgetService->validateBudgetData($request->all());
            
            // Create the budget using the service
            $budget = $this->budgetService->createBudget($user, $team, $validatedData);

            return response()->json([
                'message' => 'Budget created successfully',
                'budget' => $budget,
            ], 201);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Update a budget
     */
    public function update(Request $request, Team $team, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Validate the request data
            $validatedData = $this->budgetService->validateBudgetData($request->all(), true);
            
            // Update the budget using the service
            $updatedBudget = $this->budgetService->updateBudget($user, $team, $budget, $validatedData);

            return response()->json([
                'message' => 'Budget updated successfully',
                'budget' => $updatedBudget,
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Delete a budget
     */
    public function destroy(Team $team, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Delete the budget using the service
            $this->budgetService->deleteBudget($user, $team, $budget);

            return response()->json([
                'message' => 'Budget deleted successfully'
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Archive a budget
     */
    public function archive(Team $team, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Archive the budget using the service
            $archivedBudget = $this->budgetService->archiveBudget($user, $team, $budget);

            return response()->json([
                'message' => 'Budget archived successfully',
                'budget' => $archivedBudget,
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Get budget summary
     */
    public function summary(Request $request, Team $team, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            $period = $request->get('period', 'month');
            
            // Validate period
            if (!in_array($period, ['month', 'quarter', 'year'])) {
                return response()->json([
                    'message' => 'Invalid period. Must be month, quarter, or year'
                ], 400);
            }
            
            $summary = $this->budgetService->getBudgetSummary($user, $team, $budget, $period);

            return response()->json($summary);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }
}
