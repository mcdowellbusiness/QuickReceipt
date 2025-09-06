<?php

namespace App\Services;

use App\Contracts\FileStorageService;
use App\Exceptions\TeamException;
use App\Models\Budget;
use App\Models\Receipt;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ReceiptService
{
    protected $authService;
    protected $fileStorageService;

    public function __construct(AuthorizationService $authService, FileStorageService $fileStorageService)
    {
        $this->authService = $authService;
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Get receipts for a budget (filtered by user permissions)
     */
    public function getBudgetReceipts(User $user, Budget $budget, array $filters = []): Collection
    {
        // Check if user has access to this budget's team
        if (!$this->authService->hasTeamAccess($user, $budget->team)) {
            throw new TeamException('You do not have access to this budget', 403);
        }

        $query = Receipt::where('budget_id', $budget->id)
            ->with(['file', 'budget', 'transaction.user', 'transaction.category', 'transaction.budget']);

        // If user is not admin, only show receipts for their own transactions
        if (!$this->canManageReceipts($user, $budget->team)) {
            $query->whereHas('transaction', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Apply filters
        if (isset($filters['transaction_id'])) {
            $query->where('transaction_id', $filters['transaction_id']);
        }

        if (isset($filters['has_receipt'])) {
            if ($filters['has_receipt']) {
                $query->whereNotNull('file_id');
            } else {
                $query->whereNull('file_id');
            }
        }

        $receipts = $query->orderBy('created_at', 'desc')->get();

        // Add URLs to each receipt
        $receipts->each(function ($receipt) {
            $receipt->url = $receipt->getUrl();
        });

        return $receipts;
    }

    /**
     * Get a specific receipt
     */
    public function getReceipt(User $user, Budget $budget, Receipt $receipt): Receipt
    {
        // Check if user has access to this budget's team
        if (!$this->authService->hasTeamAccess($user, $budget->team)) {
            throw new TeamException('You do not have access to this budget', 403);
        }

        // Verify receipt belongs to this budget
        if ($receipt->budget_id !== $budget->id) {
            throw new TeamException('Receipt does not belong to this budget', 404);
        }

        // Check if user can view this receipt
        if (!$this->canViewReceipt($user, $budget->team, $receipt)) {
            throw new TeamException('You can only view receipts for your own transactions', 403);
        }

        return $receipt->load(['file', 'transaction.user', 'transaction.category', 'transaction.budget']);
    }

    /**
     * Upload a receipt (creates receipt first, then can be linked to transaction)
     */
    public function uploadReceipt(User $user, Budget $budget, UploadedFile $file): Receipt
    {
        // Check if user has access to this budget's team
        if (!$this->authService->hasTeamAccess($user, $budget->team)) {
            throw new TeamException('You do not have access to this budget', 403);
        }

        // Validate file
        $this->validateReceiptFile($file);

        return DB::transaction(function () use ($file, $budget) {
            // Store the file
            $fileRecord = $this->fileStorageService->store($file, 'receipts');

            // Create receipt record
            $receipt = Receipt::create([
                'budget_id' => $budget->id,
                'file_id' => $fileRecord->id,
            ]);

            return $receipt;
        });
    }

    /**
     * Replace a receipt
     */
    public function replaceReceipt(User $user, Budget $budget, Receipt $receipt, UploadedFile $file): Receipt
    {
        // Check if user can manage receipts
        if (!$this->canManageReceipts($user, $budget->team)) {
            throw new TeamException('You must be a team admin or organization admin to replace receipts', 403);
        }

        // Verify receipt belongs to this budget
        if ($receipt->budget_id !== $budget->id) {
            throw new TeamException('Receipt does not belong to this budget', 404);
        }

        // Validate file
        $this->validateReceiptFile($file);

        return DB::transaction(function () use ($receipt, $file) {
            // If there's an existing file, replace it
            if ($receipt->file) {
                $this->fileStorageService->replace($receipt->file->id, $file);
                $receipt->file->refresh();
            } else {
                // Create new file record
                $fileRecord = $this->fileStorageService->store($file, 'receipts');
                $receipt->update(['file_id' => $fileRecord->id]);
            }

            return $receipt->load(['file', 'transaction.user', 'transaction.category', 'transaction.budget']);
        });
    }

    /**
     * Delete a receipt
     */
    public function deleteReceipt(User $user, Budget $budget, Receipt $receipt): bool
    {
        // Check if user can manage receipts
        if (!$this->canManageReceipts($user, $budget->team)) {
            throw new TeamException('You must be a team admin or organization admin to delete receipts', 403);
        }

        // Verify receipt belongs to this budget
        if ($receipt->budget_id !== $budget->id) {
            throw new TeamException('Receipt does not belong to this budget', 404);
        }

        return DB::transaction(function () use ($receipt) {
            // Delete the file if it exists
            if ($receipt->file) {
                $this->fileStorageService->delete($receipt->file->id);
            }

            // Delete the receipt record
            return $receipt->delete();
        });
    }

    /**
     * Get receipt URL
     */
    public function getReceiptUrl(User $user, Budget $budget, Receipt $receipt): string
    {
        // Check if user can view this receipt
        if (!$this->canViewReceipt($user, $budget->team, $receipt)) {
            throw new TeamException('You can only view receipts for your own transactions', 403);
        }

        if (!$receipt->file) {
            throw new TeamException('No file associated with this receipt', 404);
        }

        return $this->fileStorageService->getUrl($receipt->file->id);
    }

    /**
     * Check if user can manage receipts (team admin or org admin)
     */
    private function canManageReceipts(User $user, \App\Models\Team $team): bool
    {
        return $this->authService->canManageBudgets($user, $team);
    }

    /**
     * Check if user can view a specific receipt
     */
    private function canViewReceipt(User $user, \App\Models\Team $team, Receipt $receipt): bool
    {
        // Admins can view all receipts
        if ($this->canManageReceipts($user, $team)) {
            return true;
        }

        // Regular members can only view receipts for their own transactions
        return $receipt->transaction->user_id === $user->id;
    }

    /**
     * Check if user can upload receipt for a transaction
     */
    private function canUploadReceipt(User $user, \App\Models\Team $team, Transaction $transaction): bool
    {
        // Admins can upload receipts for any transaction
        if ($this->canManageReceipts($user, $team)) {
            return true;
        }

        // Regular members can only upload receipts for their own transactions
        return $transaction->user_id === $user->id;
    }

    /**
     * Validate receipt file
     */
    private function validateReceiptFile(UploadedFile $file): void
    {
        $validator = validator(['file' => $file], [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            throw new TeamException('Invalid file: ' . implode(', ', $validator->errors()->all()), 422);
        }
    }
}
