<?php

namespace App\Http\Controllers\OrgManagement;

use App\Exceptions\TeamException;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    protected $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * List receipts for a budget
     */
    public function index(Request $request, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            // Get filters from request
            $filters = $request->only([
                'transaction_id',
                'has_receipt'
            ]);

            $receipts = $this->receiptService->getBudgetReceipts($user, $budget, $filters);

            return response()->json($receipts);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Show a specific receipt
     */
    public function show(Budget $budget, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $receipt = $this->receiptService->getReceipt($user, $budget, $receipt);

            return response()->json([
                'receipt' => $receipt,
                'url' => $receipt->getUrl(),
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Upload a receipt
     */
    public function upload(Request $request, Budget $budget)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            $receipt = $this->receiptService->uploadReceipt($user, $budget, $request->file('file'));

            return response()->json([
                'message' => 'Receipt uploaded successfully',
                'receipt' => $receipt,
                'url' => $receipt->getUrl(),
            ], 201);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Replace a receipt
     */
    public function replace(Request $request, Budget $budget, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            $receipt = $this->receiptService->replaceReceipt($user, $budget, $receipt, $request->file('file'));

            return response()->json([
                'message' => 'Receipt replaced successfully',
                'receipt' => $receipt,
                'url' => $receipt->getUrl(),
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Delete a receipt
     */
    public function destroy(Budget $budget, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $this->receiptService->deleteReceipt($user, $budget, $receipt);

            return response()->json([
                'message' => 'Receipt deleted successfully'
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Get receipt URL
     */
    public function url(Budget $budget, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $url = $this->receiptService->getReceiptUrl($user, $budget, $receipt);

            return response()->json([
                'url' => $url
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }
}
