<?php

namespace App\Services;

use App\Exceptions\TeamException;
use App\Models\Budget;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetService
{
    protected $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get all budgets for a team (both active and archived)
     */
    public function getTeamBudgets(User $user, Team $team): Collection
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        return Budget::where('team_id', $team->id)
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a specific budget
     */
    public function getBudget(User $user, Team $team, Budget $budget): Budget
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify budget belongs to team
        if ($budget->team_id !== $team->id) {
            throw new TeamException('Budget does not belong to this team', 404);
        }

        return $budget;
    }

    /**
     * Create a new budget
     */
    public function createBudget(User $user, Team $team, array $data): Budget
    {
        // Check if user can create budgets (team admin or org admin)
        if (!$this->authService->canManageBudgets($user, $team)) {
            throw new TeamException('You must be a team admin or organization admin to create budgets', 403);
        }

        return DB::transaction(function () use ($team, $data) {
            return Budget::create([
                'team_id' => $team->id,
                'year' => $data['year'],
                'total_limit_cents' => $this->convertToCents($data['total_limit']),
                'status' => 'active',
            ]);
        });
    }

    /**
     * Update a budget
     */
    public function updateBudget(User $user, Team $team, Budget $budget, array $data): Budget
    {
        // Check if user can manage budgets
        if (!$this->authService->canManageBudgets($user, $team)) {
            throw new TeamException('You must be a team admin or organization admin to update budgets', 403);
        }

        // Verify budget belongs to team
        if ($budget->team_id !== $team->id) {
            throw new TeamException('Budget does not belong to this team', 404);
        }

        $updateData = [];
        if (isset($data['year'])) {
            $updateData['year'] = $data['year'];
        }
        if (isset($data['total_limit'])) {
            $updateData['total_limit_cents'] = $this->convertToCents($data['total_limit']);
        }

        $budget->update($updateData);
        return $budget;
    }

    /**
     * Delete a budget
     */
    public function deleteBudget(User $user, Team $team, Budget $budget): bool
    {
        // Check if user can manage budgets
        if (!$this->authService->canManageBudgets($user, $team)) {
            throw new TeamException('You must be a team admin or organization admin to delete budgets', 403);
        }

        // Verify budget belongs to team
        if ($budget->team_id !== $team->id) {
            throw new TeamException('Budget does not belong to this team', 404);
        }

        return $budget->delete();
    }

    /**
     * Archive a budget
     */
    public function archiveBudget(User $user, Team $team, Budget $budget): Budget
    {
        // Check if user can manage budgets
        if (!$this->authService->canManageBudgets($user, $team)) {
            throw new TeamException('You must be a team admin or organization admin to archive budgets', 403);
        }

        // Verify budget belongs to team
        if ($budget->team_id !== $team->id) {
            throw new TeamException('Budget does not belong to this team', 404);
        }

        $budget->update(['status' => 'archived']);
        return $budget;
    }

    /**
     * Get budget summary for current period
     */
    public function getBudgetSummary(User $user, Team $team, Budget $budget, string $period = 'month'): array
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify budget belongs to team
        if ($budget->team_id !== $team->id) {
            throw new TeamException('Budget does not belong to this team', 404);
        }

        $now = Carbon::now();
        $dateRange = $this->getDateRangeForPeriod($period, $now);
        
        // Get transactions for this budget within the period
        $transactions = Transaction::where('budget_id', $budget->id)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Calculate spending
        $spentAmount = $this->calculateSpentAmount($transactions);
        
        // Calculate budget allocation for period
        $budgetAllocation = $this->calculateBudgetAllocation($budget->total_limit_cents, $period, $now);
        
        // Calculate amount left to spend
        $amountLeft = $budgetAllocation - $spentAmount;
        
        // Calculate spending status
        $spendingStatus = $this->calculateSpendingStatus($spentAmount, $budgetAllocation);
        
        // Calculate if on track for the year
        $onTrackStatus = $this->calculateOnTrackStatus($budget, $now);

        return [
            'budget' => $budget,
            'period' => $period,
            'period_info' => $this->getPeriodInfo($period, $now),
            'budget_allocation_cents' => $budgetAllocation,
            'budget_allocation_dollars' => $this->convertFromCents($budgetAllocation),
            'spent_amount_cents' => $spentAmount,
            'spent_amount_dollars' => $this->convertFromCents($spentAmount),
            'amount_left_cents' => $amountLeft,
            'amount_left_dollars' => $this->convertFromCents($amountLeft),
            'spending_status' => $spendingStatus,
            'on_track_status' => $onTrackStatus,
            'transactions_count' => $transactions->count(),
        ];
    }


    /**
     * Convert dollars to cents
     */
    private function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert cents to dollars
     */
    private function convertFromCents(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * Get date range for period
     */
    private function getDateRangeForPeriod(string $period, Carbon $now): array
    {
        switch ($period) {
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'quarter':
                $quarter = $now->quarter;
                $startMonth = ($quarter - 1) * 3 + 1;
                return [
                    'start' => $now->copy()->month($startMonth)->startOfMonth(),
                    'end' => $now->copy()->month($startMonth + 2)->endOfMonth(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            default:
                throw new TeamException('Invalid period. Must be month, quarter, or year', 400);
        }
    }

    /**
     * Calculate budget allocation for period
     */
    private function calculateBudgetAllocation(int $totalLimitCents, string $period, Carbon $now): int
    {
        switch ($period) {
            case 'month':
                return (int) round($totalLimitCents / 12);
            case 'quarter':
                return (int) round($totalLimitCents / 4);
            case 'year':
                return $totalLimitCents;
            default:
                return 0;
        }
    }

    /**
     * Calculate spent amount from transactions
     */
    private function calculateSpentAmount(Collection $transactions): int
    {
        return $transactions->sum(function ($transaction) {
            // Expenses add to spending, Income subtracts from spending
            return $transaction->type === 'expense' 
                ? $transaction->amount_cents 
                : -$transaction->amount_cents;
        });
    }

    /**
     * Calculate spending status
     */
    private function calculateSpendingStatus(int $spentAmount, int $budgetAllocation): string
    {
        $percentage = $budgetAllocation > 0 ? ($spentAmount / $budgetAllocation) * 100 : 0;
        
        if ($percentage <= 80) {
            return 'Under Budget';
        } elseif ($percentage <= 100) {
            return 'On Track';
        } else {
            return 'Over Budget';
        }
    }

    /**
     * Calculate if on track for the year
     */
    private function calculateOnTrackStatus(Budget $budget, Carbon $now): string
    {
        // Get all transactions for this budget this year
        $yearTransactions = Transaction::where('budget_id', $budget->id)
            ->whereYear('date', $now->year)
            ->get();

        $totalSpentThisYear = $this->calculateSpentAmount($yearTransactions);
        
        // Calculate months passed this year
        $monthsPassed = $now->month;
        $monthsInYear = 12;
        
        // Calculate expected spending by now
        $expectedSpendingByNow = (int) round(($budget->total_limit_cents / $monthsInYear) * $monthsPassed);
        
        // Calculate spending pace
        $spendingPace = $monthsPassed > 0 ? $totalSpentThisYear / $monthsPassed : 0;
        $projectedYearEndSpending = (int) round($spendingPace * $monthsInYear);
        
        if ($projectedYearEndSpending <= $budget->total_limit_cents) {
            return 'On Track';
        } else {
            return 'Off Track';
        }
    }

    /**
     * Get period information
     */
    private function getPeriodInfo(string $period, Carbon $now): array
    {
        switch ($period) {
            case 'month':
                return [
                    'type' => 'month',
                    'month' => $now->month,
                    'year' => $now->year,
                    'name' => $now->format('F Y'),
                ];
            case 'quarter':
                return [
                    'type' => 'quarter',
                    'quarter' => $now->quarter,
                    'year' => $now->year,
                    'name' => 'Q' . $now->quarter . ' ' . $now->year,
                ];
            case 'year':
                return [
                    'type' => 'year',
                    'year' => $now->year,
                    'name' => (string) $now->year,
                ];
            default:
                return [];
        }
    }

    /**
     * Validate budget data
     */
    public function validateBudgetData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'year' => $isUpdate ? 'sometimes|required|integer|min:2020|max:2030' : 'required|integer|min:2020|max:2030',
            'total_limit' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
        ];

        $validator = validator($data, $rules);
        
        if ($validator->fails()) {
            throw new TeamException('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
        }

        return $validator->validated();
    }
}
