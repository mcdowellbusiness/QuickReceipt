<?php

namespace App\Http\Controllers\OrgManagement;

use App\Exceptions\TeamException;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * List transactions for a budget
     */
    public function index(Request $request, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Get filters from request
            $filters = $request->only([
                'category_id', 
                'type',
                'date_from',
                'date_to',
                'vendor'
            ]);

            $transactions = $this->transactionService->getBudgetTransactions($user, $budget, $filters);

            return response()->json($transactions);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Show a specific transaction
     */
    public function show(Budget $budget, Transaction $transaction)
    {
        try {
            $user = Auth::user();
            
            $transaction = $this->transactionService->getTransaction($user, $budget, $transaction);

            return response()->json($transaction);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Create a new transaction
     */
    public function store(Request $request, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Validate the request data
            $validatedData = $this->transactionService->validateTransactionData($request->all());
            
            // Create the transaction using the service
            $transaction = $this->transactionService->createTransaction($user, $budget, $validatedData);

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction,
            ], 201);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Update a transaction
     */
    public function update(Request $request, Budget $budget, Transaction $transaction)
    {
        try {
            $user = Auth::user();
            
            // Validate the request data
            $validatedData = $this->transactionService->validateTransactionData($request->all(), true);
            
            // Update the transaction using the service
            $updatedTransaction = $this->transactionService->updateTransaction($user, $budget, $transaction, $validatedData);

            return response()->json([
                'message' => 'Transaction updated successfully',
                'transaction' => $updatedTransaction,
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Delete a transaction
     */
    public function destroy(Budget $budget, Transaction $transaction)
    {
        try {
            $user = Auth::user();
            
            // Delete the transaction using the service
            $this->transactionService->deleteTransaction($user, $budget, $transaction);

            return response()->json([
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }
}
