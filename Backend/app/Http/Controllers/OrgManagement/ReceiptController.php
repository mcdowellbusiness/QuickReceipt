<?php

namespace App\Http\Controllers\OrgManagement;

use App\Exceptions\TeamException;
use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\Team;
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
     * List receipts for a team
     */
    public function index(Request $request, Team $team)
    {
        try {
            $user = Auth::user();
            
            // Get filters from request
            $filters = $request->only([
                'transaction_id',
                'has_receipt'
            ]);

            $receipts = $this->receiptService->getTeamReceipts($user, $team, $filters);

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
    public function show(Team $team, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $receipt = $this->receiptService->getReceipt($user, $team, $receipt);

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
    public function upload(Request $request, Team $team)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            $receipt = $this->receiptService->uploadReceipt($user, $team, $request->file('file'));

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
    public function replace(Request $request, Team $team, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            $receipt = $this->receiptService->replaceReceipt($user, $team, $receipt, $request->file('file'));

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
    public function destroy(Team $team, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $this->receiptService->deleteReceipt($user, $team, $receipt);

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
    public function url(Team $team, Receipt $receipt)
    {
        try {
            $user = Auth::user();
            
            $url = $this->receiptService->getReceiptUrl($user, $team, $receipt);

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
