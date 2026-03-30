<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentPosting;
use App\Models\AccountCurrentBalance;
use App\Models\AccountNumber;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvestmentPostingController extends Controller
{
    // public function getInvestmentLedgerData(Request $request)
    // {
    //     $filters = $request->query();


    //     // Use a closure to apply filters to different queries
    //     $applyFilters = function ($query) use ($filters) {
    //         $query->where('status', 'approved');


    //         if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
    //             $query->where('entry_type', $filters['filter']['transaction_type']);
    //         }


    //         if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //             $query->where('head_id', $filters['filter']['head_id']);
    //         }


    //         if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //             $query->whereBetween('posting_date', [$filters['filter']['start_date'], $filters['filter']['end_date']]);
    //         }


    //         return $query;
    //     };


    //     // Get total count of filtered records
    //     $total = $applyFilters(DB::table('investment_postings'))->count();


    //     // 1. Build and execute the summary query.
    //     $summary = $applyFilters(DB::table('investment_postings'))
    //         ->selectRaw('
    //          SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END) AS total_investment,
    //          SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS total_returned,
    //          SUM(CASE WHEN entry_type = "investment_profit" THEN amount_bdt ELSE 0 END) AS total_profit,
    //          SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END)  - SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS balance
    //      ')
    //         ->first();


    //     // Get pagination and page size from query params
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;


    //     // 2. Build and execute the detailed query.
    //     $details = $applyFilters(
    //         DB::table('investment_postings as ip')
    //             ->leftJoin('investment_parties as ipp', 'ipp.id', '=', 'ip.head_id')
    //     )
    //         ->select(
    //             'ip.id',
    //             'ip.transaction_type',
    //             'ip.entry_type',
    //             'ipp.party_name',
    //             'ip.amount_bdt',
    //             'ip.posting_date',
    //             'ip.note',
    //             'ip.status'
    //         )
    //         ->orderBy('ip.posting_date', 'DESC')
    //         ->orderBy('ip.id', 'DESC')
    //         ->offset(($page - 1) * $pageSize)
    //         ->limit($pageSize)
    //         ->get();


    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //         'total'   => $total,
    //     ]);
    // }

    public function getInvestmentLedgerData(Request $request)
    {
        $filters = $request->query();

        // -------------------------------
        // 👉 Default current month date range
        // -------------------------------
        if (
            !isset($filters['filter']['start_date']) ||
            !isset($filters['filter']['end_date'])
        ) {
            $filters['filter']['start_date'] = date('Y-m-01');             // First day of current month
            $filters['filter']['end_date'] = date('Y-m-t');                // Last day of current month
        }

        // Apply filters dynamically
        $applyFilters = function ($query) use ($filters) {
            $query->where('status', 'approved');

            if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
                $query->where('entry_type', $filters['filter']['transaction_type']);
            }

            if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
                $query->where('head_id', $filters['filter']['head_id']);
            }

            if (
                isset($filters['filter']['start_date']) &&
                isset($filters['filter']['end_date'])
            ) {
                $query->whereBetween('posting_date', [
                    $filters['filter']['start_date'],
                    $filters['filter']['end_date']
                ]);
            }

            return $query;
        };

        // -------------------------------
        // 👉 Total Count
        // -------------------------------
        $total = $applyFilters(DB::table('investment_postings'))->count();

        // -------------------------------
        // 👉 Summary
        // -------------------------------
        $summary = $applyFilters(DB::table('investment_postings'))
            ->selectRaw('
                SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END) AS total_investment,
                SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS total_returned,
                SUM(CASE WHEN entry_type = "investment_profit" THEN amount_bdt ELSE 0 END) AS total_profit,
                SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END)
                - SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS balance
            ')
            ->first();

        // -------------------------------
        // 👉 Pagination
        // -------------------------------
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['pageSize'] ?? 10;

        // -------------------------------
        // 👉 Details Query
        // -------------------------------
        $details = $applyFilters(
            DB::table('investment_postings as ip')
                ->leftJoin('investment_parties as ipp', 'ipp.id', '=', 'ip.head_id')
        )
            ->select(
                'ip.id',
                'ip.transaction_type',
                'ip.entry_type',
                'ipp.party_name',
                'ip.amount_bdt',
                'ip.posting_date',
                'ip.note',
                'ip.status'
            )
            ->orderBy('ip.posting_date', 'DESC')
            ->orderBy('ip.id', 'DESC')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return response()->json([
            'summary' => $summary,
            'details' => $details,
            'total'   => $total,
            'default_month' => [
                'start_date' => $filters['filter']['start_date'],
                'end_date'   => $filters['filter']['end_date'],
            ]
        ]);
    }



    // public function getInvestmentLedgerDataSummary(Request $request)
    // {
    //     $filters = $request->query();

        
    //     $applyFilters = function ($query) use ($filters) {
    //         $query->where('status', 'approved');

    //         if (!empty($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
    //             $query->where('entry_type', $filters['filter']['transaction_type']);
    //         }

    //         if (!empty($filters['filter']['head_id'])) {
    //             $query->where('head_id', $filters['filter']['head_id']);
    //         }

    //         if (!empty($filters['filter']['start_date']) && !empty($filters['filter']['end_date'])) {
    //             $query->whereBetween('posting_date', [
    //                 $filters['filter']['start_date'],
    //                 $filters['filter']['end_date']
    //             ]);
    //         }

    //         return $query;
    //     };

        
    //     $summary = $applyFilters(DB::table('investment_postings'))
    //         ->selectRaw('
    //             SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END) AS total_investment,
    //             SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS total_returned,
    //             SUM(CASE WHEN entry_type = "investment_profit" THEN amount_bdt ELSE 0 END) AS total_profit,
    //             SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END)
    //             - SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS balance
    //         ')
    //         ->first();

        
    //     $details = [
    //         [
    //             'entry_type' => 'investment',
    //             'amount_bdt' => $applyFilters(
    //                 DB::table('investment_postings')->where('entry_type', 'investment')
    //             )->sum('amount_bdt')
    //         ],
    //         [
    //             'entry_type' => 'investment_return',
    //             'amount_bdt' => $applyFilters(
    //                 DB::table('investment_postings')->where('entry_type', 'investment_return')
    //             )->sum('amount_bdt')
    //         ],
    //         [
    //             'entry_type' => 'investment_profit',
    //             'amount_bdt' => $applyFilters(
    //                 DB::table('investment_postings')->where('entry_type', 'investment_profit')
    //             )->sum('amount_bdt')
    //         ]
    //     ];

    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //     ]);
    // }


    public function getInvestmentLedgerDataSummary(Request $request)
    {
        $filters = $request->query();

        
        $applyFilters = function ($query) use ($filters) {
            $query->where('status', 'approved');

            if (!empty($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
                $query->where('entry_type', $filters['filter']['transaction_type']);
            }

            if (!empty($filters['filter']['head_id'])) {
                $query->where('head_id', $filters['filter']['head_id']);
            }

            if (!empty($filters['filter']['start_date']) && !empty($filters['filter']['end_date'])) {
                $query->whereBetween('posting_date', [
                    $filters['filter']['start_date'],
                    $filters['filter']['end_date']
                ]);
            }

            return $query;
        };


    
        $summary = $applyFilters(DB::table('investment_postings'))
            ->selectRaw('
                SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END) AS total_investment,
                SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS total_returned,
                SUM(CASE WHEN entry_type = "investment_profit" THEN amount_bdt ELSE 0 END) AS total_profit,
                SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END)
                - SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS balance
            ')
            ->first();


        
        $details = $applyFilters(
            DB::table('investment_postings as ip')
                ->leftJoin('investment_parties as p', 'p.id', '=', 'ip.head_id')
        )
            ->selectRaw('
                ip.head_id,
                p.party_name,
                SUM(CASE WHEN ip.entry_type = "investment" THEN ip.amount_bdt ELSE 0 END) AS total_investment,
                SUM(CASE WHEN ip.entry_type = "investment_return" THEN ip.amount_bdt ELSE 0 END) AS total_returned,
                SUM(CASE WHEN ip.entry_type = "investment_profit" THEN ip.amount_bdt ELSE 0 END) AS total_profit,
                SUM(CASE WHEN ip.entry_type = "investment" THEN ip.amount_bdt ELSE 0 END)
                - SUM(CASE WHEN ip.entry_type = "investment_return" THEN ip.amount_bdt ELSE 0 END) AS balance
            ')
            ->groupBy('ip.head_id', 'p.party_name')
            ->get();


        return response()->json([
            'summary' => $summary,
            'details' => $details,
        ]);
    }


    // public function getInvestmentLedgerDataSummary(Request $request)
    // {
    //     $filters = $request->query();

    //     // --- Set default date range (current month) ---
    //     $defaultStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    //     $defaultEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
    //     // Determine if we're using default dates
    //     $usingDefaultDates = !isset($filters['filter']['start_date']) && !isset($filters['filter']['end_date']);

    //     // Set dates in filters array if not provided
    //     if ($usingDefaultDates) {
    //         if (!isset($filters['filter'])) {
    //             $filters['filter'] = [];
    //         }
    //         $filters['filter']['start_date'] = $defaultStartDate;
    //         $filters['filter']['end_date'] = $defaultEndDate;
    //     }

    //     $applyFilters = function ($query) use ($filters) {
    //         $query->where('status', 'approved');

    //         if (!empty($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
    //             $query->where('entry_type', $filters['filter']['transaction_type']);
    //         }

    //         if (!empty($filters['filter']['head_id'])) {
    //             $query->where('head_id', $filters['filter']['head_id']);
    //         }

    //         // Always apply date filter (either from request or defaults)
    //         if (!empty($filters['filter']['start_date']) && !empty($filters['filter']['end_date'])) {
    //             $query->whereBetween('posting_date', [
    //                 $filters['filter']['start_date'],
    //                 $filters['filter']['end_date']
    //             ]);
    //         }

    //         return $query;
    //     };

    //     // Summary query
    //     $summary = $applyFilters(DB::table('investment_postings'))
    //         ->selectRaw('
    //             SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END) AS total_investment,
    //             SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS total_returned,
    //             SUM(CASE WHEN entry_type = "investment_profit" THEN amount_bdt ELSE 0 END) AS total_profit,
    //             SUM(CASE WHEN entry_type = "investment" THEN amount_bdt ELSE 0 END)
    //             - SUM(CASE WHEN entry_type = "investment_return" THEN amount_bdt ELSE 0 END) AS balance
    //         ')
    //         ->first();

    //     // Details query
    //     $details = $applyFilters(
    //         DB::table('investment_postings as ip')
    //             ->leftJoin('investment_parties as p', 'p.id', '=', 'ip.head_id')
    //     )
    //         ->selectRaw('
    //             ip.head_id,
    //             p.party_name,
    //             SUM(CASE WHEN ip.entry_type = "investment" THEN ip.amount_bdt ELSE 0 END) AS total_investment,
    //             SUM(CASE WHEN ip.entry_type = "investment_return" THEN ip.amount_bdt ELSE 0 END) AS total_returned,
    //             SUM(CASE WHEN ip.entry_type = "investment_profit" THEN ip.amount_bdt ELSE 0 END) AS total_profit,
    //             SUM(CASE WHEN ip.entry_type = "investment" THEN ip.amount_bdt ELSE 0 END)
    //             - SUM(CASE WHEN ip.entry_type = "investment_return" THEN ip.amount_bdt ELSE 0 END) AS balance
    //         ')
    //         ->groupBy('ip.head_id', 'p.party_name')
    //         ->get();

    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //         'date_range' => [
    //             'start_date' => $filters['filter']['start_date'] ?? null,
    //             'end_date' => $filters['filter']['end_date'] ?? null,
    //             'is_default_range' => $usingDefaultDates
    //         ]
    //     ]);
    // }





    public function getInvestmentCalculation(int $investment_party_id)
    {
        // 1. Find the single approved 'investment' posting for this head_id.
        $mainInvestmentPosting = InvestmentPosting::where('head_id', $investment_party_id)
            ->where('entry_type', 'investment')
            ->where('status', 'approved')
            ->with([
                'investment.postings' => function ($query) {
                    $query->whereIn('entry_type', ['investment_return', 'investment_profit']);
                }
            ])
            ->first();
        // return $mainInvestmentPosting;

        // If no main investment posting is found, return a default response.
        if (!$mainInvestmentPosting) {
            return response()->json([
                'total_investment' => 0,
                'total_returns' => 0,
                'total_profit' => 0,
                'remaining' => 0,
                'net_profit_or_loss' => 0,
                'investment_id' => null,
                'investment_party_id' => $investment_party_id,
            ]);
        }

        // Get the initial investment amount from the main posting.
        $totalInvestment = (float) $mainInvestmentPosting->investment->principal_amount;

        // Initialize variables for returns and profits.
        $totalReturns = 0;
        $totalProfit = 0;

        // Loop through the related postings to sum returns and profits.
        foreach ($mainInvestmentPosting->investment->postings as $posting) {
            if ($posting->entry_type === 'investment_return') {
                $totalReturns += (float) $posting->amount_bdt;
            } elseif ($posting->entry_type === 'investment_profit') {
                $totalProfit += (float) $posting->amount_bdt;
            }
        }

        // Calculate the remaining amount and net profit/loss.
        $remaining = $totalInvestment - $totalReturns;
        $netProfitOrLoss = $totalReturns + $totalProfit - $totalInvestment;

        return response()->json([
            'total_investment' => $totalInvestment,
            'total_returns' => $totalReturns,
            'total_profit' => $totalProfit,
            'remaining' => max(0, $remaining), // Ensure remaining is not negative.
            'net_profit_or_loss' => $netProfitOrLoss,
            'investment_id' => $mainInvestmentPosting->investment_id,
            'investment_party_id' => $investment_party_id,
        ]);
    }

    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $status = $request->input('status');

        if (empty($status) && $status !== 'all') {
            $status = 'pending';
        }

        $query = DB::table('investment_postings as iep')
            ->leftJoin('investment_parties as ih', 'ih.id', '=', 'iep.head_id')
            ->join('payment_channel_details as pcd', 'pcd.id', '=', 'iep.payment_channel_id')
            ->leftJoin('account_numbers as ac', 'ac.id', '=', 'iep.account_id')
            ->select(
                'iep.*',
                'ih.party_name',
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
    //     $mapping = [
    //         'investment'        => 'payment',
    //         'investment_return' => 'received',
    //         'investment_profit' => 'received',
    //     ];

    //     try {
    //         DB::beginTransaction();
    //         $lnvestmentId = $request->input('investment_id');

    //         if ($request->input('transaction_type') === 'investment') {
    //             $investment = Investment::create([
    //                 'principal_amount' => $request->input('amount_bdt'),
    //                 'investment_start_date'  => $request->input('posting_date'),
    //                 'status'           => 'active',
    //             ]);
    //             $lnvestmentId = $investment->id;
    //         }
    //         $posting = InvestmentPosting::create([
    //             'transaction_type'   => $mapping[$request->input('transaction_type')],
    //             'entry_type'         => $request->input('transaction_type'),
    //             'head_id'            => $request->input('head_id'),
    //             'payment_channel_id' => $request->input('payment_channel_id'),
    //             'account_id'         => $request->input('account_id'),
    //             'receipt_number'     => $request->input('receipt_number'),
    //             'amount_bdt'         => $request->input('amount_bdt'),
    //             'posting_date'       => $request->input('posting_date'),
    //             'investment_id'      => $lnvestmentId,
    //             'note'               => $request->input('note'),
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Created successfully.',
    //             'data'    => $posting
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $mapping = [
    //         'investment'        => 'payment',
    //         'investment_return' => 'received',
    //         'investment_profit' => 'received',
    //     ];

    //     try {
    //         DB::beginTransaction();
    //         $lnvestmentId = $request->input('investment_id');

    //         if ($request->input('transaction_type') === 'investment') {
    //             $investment = Investment::create([
    //                 'principal_amount' => $request->input('amount_bdt'),
    //                 'investment_start_date'  => $request->input('posting_date'),
    //                 'status'           => 'active',
    //             ]);
    //             $lnvestmentId = $investment->id;
    //         }
    //         $posting = InvestmentPosting::create([
    //             'transaction_type'   => $mapping[$request->input('transaction_type')],
    //             'entry_type'         => $request->input('transaction_type'),
    //             'head_id'            => $request->input('head_id'),
    //             'payment_channel_id' => $request->input('payment_channel_id'),
    //             'account_id'         => $request->input('account_id'),
    //             'receipt_number'     => $request->input('receipt_number'),
    //             'amount_bdt'         => $request->input('amount_bdt'),
    //             'posting_date'       => $request->input('posting_date'),
    //             'investment_id'      => $lnvestmentId,
    //             'note'               => $request->input('note'),
    //         ]);

    //         // Update Account Current Balance
    //         $accountId = $request->input('account_id');
    //         $amount = (float) $request->input('amount_bdt');
    //         $transactionType = $mapping[$request->input('transaction_type')]; // Use the mapped transaction type

    //         $currentBalance = AccountCurrentBalance::where('account_id', $accountId)->first();

    //         if ($currentBalance) {
    //             if ($transactionType === 'received') {
    //                 $currentBalance->balance += $amount;
    //             } elseif ($transactionType === 'payment') {
    //                 $currentBalance->balance -= $amount;
    //             }
    //             $currentBalance->save();
    //         } else {
    //             throw new \Exception("No current balance record found for account ID: $accountId");
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Created successfully.',
    //             'data'    => $posting
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage()
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
        $mapping = [
            'investment'         => 'payment',
            'investment_return'  => 'received',
            'investment_profit'  => 'received',
        ];

        try {
            $accountId       = $request->input('account_id');
            $amount          = (float) $request->input('amount_bdt');
            $transactionType = $mapping[$request->input('transaction_type')];

            
            if ($transactionType === 'payment') { 
                try {
                    $availableBalance = $this->calculateNetBalanceForAccount($accountId);

                    if ($availableBalance < $amount) {
                        $account     = AccountNumber::find($accountId);
                        $accountName = $account ? $account->ac_name : 'The selected account';

                        return response()->json([
                            'message' => 'Insufficient funds.',
                            'error'   => "{$accountName} has an available balance of "
                                    . number_format($availableBalance, 2)
                                    . " BDT, which is less than the payment amount of "
                                    . number_format($amount, 2) . " BDT.",
                        ], 422);
                    }

                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to validate account balance.',
                        'error'   => $e->getMessage(),
                    ], 500);
                }
            }

            // -------------------------------------------------------------
            // 2. Begin DB Transaction
            // -------------------------------------------------------------
            DB::beginTransaction();

            $investmentId = $request->input('investment_id');

            // Create new investment if needed
            if ($request->input('transaction_type') === 'investment') {
                $investment = Investment::create([
                    'principal_amount'      => $request->input('amount_bdt'),
                    'investment_start_date' => $request->input('posting_date'),
                    'status'                => 'active',
                ]);

                $investmentId = $investment->id;
            }

            // Create investment posting
            $posting = InvestmentPosting::create([
                'transaction_type'   => $transactionType,
                'entry_type'         => $request->input('transaction_type'),
                'head_id'            => $request->input('head_id'),
                'payment_channel_id' => $request->input('payment_channel_id'),
                'account_id'         => $accountId,
                'receipt_number'     => $request->input('receipt_number'),
                'amount_bdt'         => $amount,
                'posting_date'       => $request->input('posting_date'),
                'investment_id'      => $investmentId,
                'note'               => $request->input('note'),
            ]);

            
            $currentBalance = AccountCurrentBalance::firstOrCreate(
                ['account_id' => $accountId],
                ['balance'    => 0]
            );

            if ($transactionType === 'received') {
                $currentBalance->balance += $amount;
            } elseif ($transactionType === 'payment') {
                $currentBalance->balance -= $amount;
            }

            $currentBalance->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Created successfully.',
                'data'    => $posting
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the source by ID
        $source = InvestmentPosting::find($id);

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
    // public function update(Request $request, $id)
    // {
    //     $mapping = [
    //         'investment'        => 'payment',
    //         'investment_return' => 'received',
    //         'investment_profit' => 'received',
    //     ];


    //     try {
    //         DB::beginTransaction();


    //         $lnvestmentId = $request->input('investment_id');


    //         if ($request->input('transaction_type') === 'investment') {
    //             $investment = Investment::findOrFail($lnvestmentId);
    //             $investment->update([
    //                 'principal_amount'      => $request->input('amount_bdt'),
    //                 'investment_start_date' => $request->input('posting_date'),
    //                 'status'                => 'active',
    //             ]);
    //         }


    //         // Find and update the existing investment posting record
    //         $posting = InvestmentPosting::findOrFail($id);
    //         $posting->update([
    //             'transaction_type'      => $mapping[$request->input('transaction_type')],
    //             'entry_type'            => $request->input('transaction_type'),
    //             'head_id'               => $request->input('head_id'),
    //             'payment_channel_id'    => $request->input('payment_channel_id'),
    //             'account_id'            => $request->input('account_id'),
    //             'receipt_number'        => $request->input('receipt_number'),
    //             'amount_bdt'            => $request->input('amount_bdt'),
    //             'posting_date'          => $request->input('posting_date'),
    //             'investment_id'         => $lnvestmentId,
    //             'note'                  => $request->input('note'),
    //         ]);


    //         DB::commit();


    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Updated successfully.',
    //             'data'    => $posting
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();


    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function update(Request $request, $id)
    {
        $mapping = [
            'investment'        => 'payment',
            'investment_return' => 'received',
            'investment_profit' => 'received',
        ];

        try {
            DB::beginTransaction();

            $lnvestmentId = $request->input('investment_id');

            if ($request->input('transaction_type') === 'investment') {
                $investment = Investment::findOrFail($lnvestmentId);
                $investment->update([
                    'principal_amount'      => $request->input('amount_bdt'),
                    'investment_start_date' => $request->input('posting_date'),
                    'status'                => 'active',
                ]);
            }

            // Find and update the existing investment posting record
            $posting = InvestmentPosting::findOrFail($id);

            // Store old values for calculation
            $oldAmount = (float) $posting->amount_bdt;
            $oldTransactionType = $posting->transaction_type;
            $accountId = $posting->account_id;

            $posting->update([
                'transaction_type'      => $mapping[$request->input('transaction_type')],
                'entry_type'            => $request->input('transaction_type'),
                'head_id'               => $request->input('head_id'),
                'payment_channel_id'    => $request->input('payment_channel_id'),
                'account_id'            => $request->input('account_id'),
                'receipt_number'        => $request->input('receipt_number'),
                'amount_bdt'            => $request->input('amount_bdt'),
                'posting_date'          => $request->input('posting_date'),
                'investment_id'         => $lnvestmentId,
                'note'                  => $request->input('note'),
            ]);

            // Get new values
            $newAmount = (float) $request->input('amount_bdt', $posting->amount_bdt);
            $newTransactionType = $mapping[$request->input('transaction_type')];

            // Update Account Current Balance
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

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Updated successfully.',
                'data'    => $posting
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $source = InvestmentPosting::find($id);

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
        $posting = InvestmentPosting::find($id);

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
        $posting = InvestmentPosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Rental posting not found.'
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
        $posting = InvestmentPosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Rental posting not found.'
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
