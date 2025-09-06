<?php

namespace App\Services;

use App\Exceptions\TeamException;
use App\Models\Transaction;
use App\Models\Team;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionService
{
    protected $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get transactions for a team (filtered by user permissions)
     */
    public function getTeamTransactions(User $user, Team $team, array $filters = []): Collection
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        $query = Transaction::where('team_id', $team->id)
            ->with(['user', 'category', 'budget', 'receipt']);

        // If user is not admin, only show their own transactions
        if (!$this->canManageTransactions($user, $team)) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if (isset($filters['budget_id'])) {
            $query->where('budget_id', $filters['budget_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (isset($filters['vendor'])) {
            $query->where('vendor', 'like', '%' . $filters['vendor'] . '%');
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a specific transaction
     */
    public function getTransaction(User $user, Team $team, Transaction $transaction): Transaction
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify transaction belongs to team
        if ($transaction->team_id !== $team->id) {
            throw new TeamException('Transaction does not belong to this team', 404);
        }

        // Check if user can view this transaction
        if (!$this->canViewTransaction($user, $team, $transaction)) {
            throw new TeamException('You can only view your own transactions', 403);
        }

        return $transaction->load(['user', 'category', 'budget', 'receipt']);
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(User $user, Team $team, array $data): Transaction
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify budget belongs to team
        if (isset($data['budget_id'])) {
            $budget = \App\Models\Budget::where('id', $data['budget_id'])
                ->where('team_id', $team->id)
                ->first();
            
            if (!$budget) {
                throw new TeamException('Budget does not belong to this team', 404);
            }
        }

        // Verify category belongs to team's org
        if (isset($data['category_id'])) {
            $category = \App\Models\Category::where('id', $data['category_id'])
                ->where('org_id', $team->org_id)
                ->first();
            
            if (!$category) {
                throw new TeamException('Category does not belong to this organization', 404);
            }
        }

        return DB::transaction(function () use ($user, $team, $data) {
            // Generate unique reference code
            $referenceCode = $this->generateReferenceCode();

            return Transaction::create([
                'org_id' => $team->org_id,
                'team_id' => $team->id,
                'budget_id' => $data['budget_id'],
                'user_id' => $user->id,
                'type' => $data['type'],
                'amount_cents' => $this->convertToCents($data['amount']),
                'date' => $data['date'],
                'vendor' => $data['vendor'],
                'memo' => $data['memo'] ?? null,
                'category_id' => $data['category_id'],
                'payment_type' => $data['payment_type'] ?? 'org_card',
                'lost_receipt' => $data['lost_receipt'] ?? false,
                'reference_code' => $referenceCode,
            ]);
        });
    }

    /**
     * Update a transaction
     */
    public function updateTransaction(User $user, Team $team, Transaction $transaction, array $data): Transaction
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify transaction belongs to team
        if ($transaction->team_id !== $team->id) {
            throw new TeamException('Transaction does not belong to this team', 404);
        }

        // Check if user can edit this transaction
        if (!$this->canEditTransaction($user, $team, $transaction)) {
            throw new TeamException('You can only edit your own transactions', 403);
        }

        // Verify budget belongs to team if updating
        if (isset($data['budget_id'])) {
            $budget = \App\Models\Budget::where('id', $data['budget_id'])
                ->where('team_id', $team->id)
                ->first();
            
            if (!$budget) {
                throw new TeamException('Budget does not belong to this team', 404);
            }
        }

        // Verify category belongs to team's org if updating
        if (isset($data['category_id'])) {
            $category = \App\Models\Category::where('id', $data['category_id'])
                ->where('org_id', $team->org_id)
                ->first();
            
            if (!$category) {
                throw new TeamException('Category does not belong to this organization', 404);
            }
        }

        $updateData = [];
        
        if (isset($data['budget_id'])) {
            $updateData['budget_id'] = $data['budget_id'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }
        if (isset($data['amount'])) {
            $updateData['amount_cents'] = $this->convertToCents($data['amount']);
        }
        if (isset($data['date'])) {
            $updateData['date'] = $data['date'];
        }
        if (isset($data['vendor'])) {
            $updateData['vendor'] = $data['vendor'];
        }
        if (isset($data['memo'])) {
            $updateData['memo'] = $data['memo'];
        }
        if (isset($data['category_id'])) {
            $updateData['category_id'] = $data['category_id'];
        }
        if (isset($data['payment_type'])) {
            $updateData['payment_type'] = $data['payment_type'];
        }
        if (isset($data['lost_receipt'])) {
            $updateData['lost_receipt'] = $data['lost_receipt'];
        }

        $transaction->update($updateData);
        return $transaction->load(['user', 'category', 'budget', 'receipt']);
    }

    /**
     * Delete a transaction
     */
    public function deleteTransaction(User $user, Team $team, Transaction $transaction): bool
    {
        // Check if user has access to this team
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        // Verify transaction belongs to team
        if ($transaction->team_id !== $team->id) {
            throw new TeamException('Transaction does not belong to this team', 404);
        }

        // Check if user can delete this transaction
        if (!$this->canEditTransaction($user, $team, $transaction)) {
            throw new TeamException('You can only delete your own transactions', 403);
        }

        return $transaction->delete();
    }

    /**
     * Check if user can manage transactions (team admin or org admin)
     */
    private function canManageTransactions(User $user, Team $team): bool
    {
        return $this->authService->canManageBudgets($user, $team);
    }

    /**
     * Check if user can view a specific transaction
     */
    private function canViewTransaction(User $user, Team $team, Transaction $transaction): bool
    {
        // Admins can view all transactions
        if ($this->canManageTransactions($user, $team)) {
            return true;
        }

        // Regular members can only view their own transactions
        return $transaction->user_id === $user->id;
    }

    /**
     * Check if user can edit a specific transaction
     */
    private function canEditTransaction(User $user, Team $team, Transaction $transaction): bool
    {
        // Admins can edit all transactions
        if ($this->canManageTransactions($user, $team)) {
            return true;
        }

        // Regular members can only edit their own transactions
        return $transaction->user_id === $user->id;
    }

    /**
     * Convert dollars to cents
     */
    private function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Generate unique reference code
     */
    private function generateReferenceCode(): string
    {
        do {
            $code = 'TXN' . strtoupper(substr(uniqid(), -8));
        } while (Transaction::where('reference_code', $code)->exists());

        return $code;
    }

    /**
     * Validate transaction data
     */
    public function validateTransactionData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'budget_id' => $isUpdate ? 'sometimes|required|exists:budgets,id' : 'required|exists:budgets,id',
            'type' => $isUpdate ? 'sometimes|required|in:expense,income' : 'required|in:expense,income',
            'amount' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'date' => $isUpdate ? 'sometimes|required|date' : 'required|date',
            'vendor' => $isUpdate ? 'sometimes|required|string|max:191' : 'required|string|max:191',
            'memo' => 'nullable|string|max:1000',
            'category_id' => $isUpdate ? 'sometimes|required|exists:categories,id' : 'required|exists:categories,id',
            'payment_type' => 'sometimes|in:org_card,personal_card',
            'lost_receipt' => 'sometimes|boolean',
        ];

        $validator = validator($data, $rules);
        
        if ($validator->fails()) {
            throw new TeamException('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
        }

        return $validator->validated();
    }
}
