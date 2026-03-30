<?php

namespace App\Http\Controllers;

use App\Models\IncomeExpensePosting;
use App\Models\AccountCurrentBalance;
use App\Models\LoanHistory;
use App\Models\AccountNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class IncomeExpensePostingController extends Controller
{
    /**
     * Display a listing of the resource.
     */



    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $status = $request->input('status');

        if (empty($status) && $status !== 'all') {
            $status = 'pending';
        }

        $query = DB::table('income_expense_postings as iep')
            ->leftJoin('income_expense_heads as ih', 'ih.id', '=', 'iep.head_id')
            ->join('payment_channel_details as pcd', 'pcd.id', '=', 'iep.payment_channel_id')
            ->leftJoin('account_numbers as ac', 'ac.id', '=', 'iep.account_id')
            ->select(
                'iep.*',
                'ih.head_name',
                'pcd.method_name',
                'ac.ac_no',
                'ac.ac_name'
            );


        if ($status !== 'all') {
            $query->where('iep.status', $status);
        }
        $query->orderBy('iep.id', 'desc');
        $source = $query->paginate($pageSize);

        return response()->json([
            'status' => true,
            'message' => 'Retrieved successfully.',
            'data' => $source->items(),
            'total' => $source->total(),
            'current_page' => $source->currentPage(),
            'last_page' => $source->lastPage(),
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {


    //     $posting = IncomeExpensePosting::create($request->all());

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Created successfully.',
    //         'data' => $posting
    //     ], 201);
    // }


    // public function store(Request $request)
    // {
    //     try {
    //         $posting = null;

    //         DB::transaction(function () use ($request, &$posting) {
                
    //             $posting = IncomeExpensePosting::create($request->all());

                
    //             $accountId = $request->input('account_id');
    //             $amount = (float) $request->input('amount_bdt');
    //             $transactionType = $request->input('transaction_type');

    //             $currentBalance = AccountCurrentBalance::where('account_id', $accountId)->first();

    //             if ($currentBalance) {
    //                 if ($transactionType === 'received') {
    //                     $currentBalance->balance += $amount;
    //                 } elseif ($transactionType === 'payment') {
    //                     $currentBalance->balance -= $amount;
    //                 }
    //                 $currentBalance->save();
    //             } else {
    //                 throw new \Exception("No current balance record found for account ID: $accountId");
    //             }
    //         });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Created successfully.',
    //             'data' => $posting
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Transaction failed. ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }



    // private function calculateNetBalanceForAccount(int $accountId): float
    // {
        
    //     $allPostingsQuery = "
    //         (
    //             SELECT account_id, transaction_type, amount_bdt FROM rental_postings
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM loan_postings
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM investment_postings
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM currency_postings
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM income_expense_postings
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM transfers
    //             UNION ALL
    //             SELECT account_id, transaction_type, amount_bdt FROM bank_deposits
    //         ) AS all_posts
    //     ";

        
    //     $result = DB::select("
    //         SELECT
    //             SUM(CASE WHEN all_posts.transaction_type = 'received' THEN all_posts.amount_bdt ELSE 0 END)
    //             -
    //             SUM(CASE WHEN all_posts.transaction_type = 'payment' THEN all_posts.amount_bdt ELSE 0 END)
    //         AS balance
    //         FROM {$allPostingsQuery}
    //         WHERE all_posts.account_id = ?
    //     ", [$accountId]);

        
    //     return (float) ($result[0]->balance ?? 0.00);
    // }


    private function calculateNetBalanceForAccount(int $accountId): float
    {
        try {
            $sql = "
                SELECT 
                    (
                        SUM(CASE WHEN all_posts.transaction_type = 'received' THEN all_posts.amount_bdt ELSE 0 END)
                        -
                        SUM(CASE WHEN all_posts.transaction_type = 'payment' THEN all_posts.amount_bdt ELSE 0 END)
                    ) AS balance
                FROM account_numbers AS an
                LEFT JOIN payment_channel_details p 
                    ON p.id = an.channel_detail_id
                LEFT JOIN (
                    SELECT account_id, transaction_type, amount_bdt
                    FROM rental_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM loan_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM investment_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM currency_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM income_expense_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM transfers
                    WHERE transaction_type IN ('payment','received')

                    UNION ALL
                    SELECT account_id, transaction_type, amount_bdt
                    FROM bank_deposits
                    WHERE status = 'approved'
                ) AS all_posts 
                ON an.id = all_posts.account_id
                WHERE an.id = ?
                GROUP BY an.id
            ";

            $result = DB::select($sql, [$accountId]);

            // If no approved transactions or account not found, return 0
            return (float) ($result[0]->balance ?? 0.00);

        } catch (\Exception $e) {
            // Log exception if needed
            return 0.00;
        }
    }



    
    public function store(Request $request)
    {
        try {
            $accountId = $request->input('account_id');
            $amount = (float) $request->input('amount_bdt');
            $transactionType = $request->input('transaction_type');

            
            try {
                $availableBalance = $this->calculateNetBalanceForAccount($accountId);

                if ($transactionType === 'payment' && $availableBalance < $amount) {

                    $account = AccountNumber::find($accountId);
                    $accountName = $account ? $account->ac_name : 'The selected account';

                    return response()->json([
                        'message' => 'Insufficient funds.',
                        'error' => "{$accountName} has an available balance of " . number_format($availableBalance, 2) . 
                                " BDT, which is less than the payment amount of " . number_format($amount, 2) . " BDT.",
                    ], 422);
                }

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to validate account balance.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // -------------------------------
            // 2. Save posting & update balances inside transaction
            // -------------------------------
            $posting = null;

            DB::transaction(function () use ($request, $accountId, $amount, $transactionType, &$posting) {

                // Store income/expense posting
                $posting = IncomeExpensePosting::create($request->all());

                // Instead of querying current_balance table directly,
                // we recalculate the balance dynamically & save it to the table.
                $currentBalance = AccountCurrentBalance::firstOrCreate(
                    ['account_id' => $accountId],
                    ['balance' => 0]
                );

                // Update balance correctly
                if ($transactionType === 'received') {
                    $currentBalance->balance += $amount;
                } elseif ($transactionType === 'payment') {
                    $currentBalance->balance -= $amount;
                }

                $currentBalance->save();
            });

            return response()->json([
                'status' => true,
                'message' => 'Created successfully.',
                'data' => $posting
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction failed. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the source by ID
        $source = IncomeExpensePosting::find($id);

        // If source not found, return error response
        if (!$source) {
            return response()->json([
                'status' => false,
                'message' => 'Source not found.'
            ], 404);
        }

        // Return the found source as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Retrieved successfully.',
            'data' => $source
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {

    //     $source = IncomeExpensePosting::find($id);

    //     if (!$source) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Source not found.'
    //         ], 404);
    //     }


    //     $source->update($request->all());

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Updated successfully.',
    //         'data' => $source
    //     ], 200);
    // }

    public function update(Request $request, string $id)
    {
        try {
            $source = IncomeExpensePosting::find($id);

            if (!$source) {
                return response()->json([
                    'status' => false,
                    'message' => 'Source not found.'
                ], 404);
            }

            DB::transaction(function () use ($request, $source) {
                // Store old values for calculation
                $oldAmount = (float) $source->amount_bdt;
                $oldTransactionType = $source->transaction_type;
                $accountId = $source->account_id;

                // Update the IncomeExpensePosting
                $source->update($request->all());

                // Get new values
                $newAmount = (float) $request->input('amount_bdt', $source->amount_bdt);
                $newTransactionType = $request->input('transaction_type', $source->transaction_type);

                // Find the current balance
                $currentBalance = AccountCurrentBalance::where('account_id', $accountId)->first();

                if (!$currentBalance) {
                    throw new \Exception("No current balance record found for account ID: $accountId");
                }

                // Calculate the difference and update balance
                $amountDifference = $newAmount - $oldAmount;
                $transactionTypeChanged = ($oldTransactionType !== $newTransactionType);

                if ($transactionTypeChanged) {
                    // If transaction type changed, reverse old transaction and apply new one
                    if ($oldTransactionType === 'received') {
                        $currentBalance->balance -= $oldAmount; // Reverse old received
                    } elseif ($oldTransactionType === 'payment') {
                        $currentBalance->balance += $oldAmount; // Reverse old payment
                    }

                    // Apply new transaction
                    if ($newTransactionType === 'received') {
                        $currentBalance->balance += $newAmount;
                    } elseif ($newTransactionType === 'payment') {
                        $currentBalance->balance -= $newAmount;
                    }
                } else {
                    // If same transaction type, just adjust the difference
                    if ($newTransactionType === 'received') {
                        $currentBalance->balance += $amountDifference;
                    } elseif ($newTransactionType === 'payment') {
                        $currentBalance->balance -= $amountDifference;
                    }
                }

                $currentBalance->save();
            });

            return response()->json([
                'status' => true,
                'message' => 'Updated successfully.',
                'data' => $source
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Update failed. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $source = IncomeExpensePosting::find($id);

        if (!$source) {
            return response()->json([
                'status' => false,
                'message' => 'Source not found.'
            ], 404);
        }

        $source->delete();
        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully.'
        ], 200);
    }

    public function statusUpdate(Request $request, string $id)
    {
        $posting = IncomeExpensePosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Posting not found.'
            ], 404);
        }

        // Get the data from the request body
        $newStatus = $request->input('status');
        $rejectionNote = $request->input('rejection_note');

        // Update the posting
        $updateData = ['status' => $newStatus];

        // Conditionally add the rejection note if provided
        if ($newStatus === 'rejected' && $rejectionNote) {
            $updateData['rejected_note'] = $rejectionNote;
            $updateData['status'] = 'pending';
        }

        $posting->update($updateData);

        return response()->json([
            'status' => true,
            'message' => 'Posting status updated successfully.',
            'data' => $posting
        ], 200);
    }


    public function softDelete(string $id)
{
    $posting = IncomeExpensePosting::find($id);

    if (!$posting) {
        return response()->json([
            'status' => false,
            'message' => 'Income/Expense posting not found.'
        ], 404);
    }

    $posting->status = 'deleted';
    $posting->save();

    return response()->json([
        'status' => true,
        'message' => 'Deleted successfully.'
    ], 200);
}

public function restore(string $id)
{
    $posting = IncomeExpensePosting::find($id);

    if (!$posting) {
        return response()->json([
            'status' => false,
            'message' => 'Income/Expense posting not found.'
        ], 404);
    }

    $posting->status = 'approved';
    $posting->save();

    return response()->json([
        'status' => true,
        'message' => 'Restored successfully.'
    ], 200);
}
}
