<?php

namespace App\Http\Controllers;

use App\Models\CurrencyPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{


    // public function financialSummaryDashboard(Request $request)
    // {
    //     $months = $request->input('months');
    //     $startDate = null;
    //     $endDate = null;

        
    //     if ($months && is_numeric($months) && $months > 0) {
    //         $startDate = now()->subMonths($months - 1)->startOfMonth();
    //         $endDate = now()->endOfMonth();
    //     }
        

    //     $dateFilter = function ($query, $column = 'posting_date') use ($startDate, $endDate) {
    //         if ($startDate && $endDate) {
    //             $query->whereBetween($column, [$startDate, $endDate]);
    //         }
    //     };

    //     $currencyQuery = DB::table('currency_postings')->where('status', 'approved');
    //     $dateFilter($currencyQuery);
    //     $currencySummary = $currencyQuery->selectRaw("
    //         SUM(CASE WHEN transaction_type='buy' THEN amount_bdt ELSE 0 END) as buy_total,
    //         SUM(CASE WHEN transaction_type='sell' THEN amount_bdt ELSE 0 END) as sell_total,
    //         SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
    //         SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
    //     ")->first();

    //     $incomeQuery = DB::table('income_expense_postings')->where('status', 'approved');
    //     $dateFilter($incomeQuery);
    //     $incomeSummary = $incomeQuery->selectRaw("
    //         SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
    //         SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
    //     ")->first();

    //     $investmentQuery = DB::table('investment_postings')->where('status', 'approved');
    //     $dateFilter($investmentQuery);
    //     $investmentSummary = $investmentQuery->selectRaw("
    //         SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
    //         SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
    //     ")->first();

    //     $rentalQuery = DB::table('rental_postings')->where('status', 'approved');
    //     $dateFilter($rentalQuery);
    //     $rentalSummary = $rentalQuery->selectRaw("
    //         SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
    //         SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
    //     ")->first();

    //     $rentExpectedQuery = DB::table('rental_house_party_maps')->where('status', 'active');
    //     $rentExpected = $rentExpectedQuery->selectRaw("
    //         SUM(
    //             CASE 
    //                 WHEN rent_start_date IS NOT NULL AND monthly_rent > 0 THEN 
    //                     ((YEAR(CURDATE()) - YEAR(STR_TO_DATE(CONCAT(rent_start_date, '-01'), '%Y-%m-%d'))) * 12
    //                      + (MONTH(CURDATE()) - MONTH(STR_TO_DATE(CONCAT(rent_start_date, '-01'), '%Y-%m-%d'))) + 1)
    //                     * monthly_rent
    //                 ELSE 0
    //             END
    //         ) as total
    //     ")->value('total');

    //     $rentSecurity = DB::table('rental_house_party_maps')->where('status', 'active')->sum('remaining_security_money');

    //     $loansGiven = DB::table('loans as l')
    //         ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
    //         ->join('loan_interest_rates as lr', 'lp.interest_rate_id', '=', 'lr.id')
    //         ->where('lp.entry_type', 'loan_given')
    //         ->where('lp.status', 'approved');
    //     $dateFilter($loansGiven, 'lp.posting_date');
    //     $loansGivenTotal = $loansGiven->selectRaw("SUM(l.principal_amount + (l.principal_amount*lr.interest_rate/100)) as total")->value('total');

    //     $loansReceived = DB::table('loans as l')
    //         ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
    //         ->where('lp.entry_type', 'loan_received')
    //         ->where('lp.status', 'approved');
    //     $dateFilter($loansReceived, 'lp.posting_date');
    //     $loansReceivedTotal = $loansReceived->sum('lp.amount_bdt');

    //     $loansTaken = DB::table('loans as l')
    //         ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
    //         ->join('loan_interest_rates as lr', 'lp.interest_rate_id', '=', 'lr.id')
    //         ->where('lp.entry_type', 'loan_taken')
    //         ->where('lp.status', 'approved');
    //     $dateFilter($loansTaken, 'lp.posting_date');
    //     $loansTakenTotal = $loansTaken->selectRaw("SUM(l.principal_amount + (l.principal_amount*lr.interest_rate/100)) as total")->value('total');

    //     $loansPayment = DB::table('loans as l')
    //         ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
    //         ->where('lp.entry_type', 'loan_payment')
    //         ->where('lp.status', 'approved');
    //     $dateFilter($loansPayment, 'lp.posting_date');
    //     $loansPaymentTotal = $loansPayment->sum('lp.amount_bdt');

    //     $totalIncome = ($currencySummary->received_total ?? 0) 
    //                  + ($incomeSummary->received_total ?? 0)
    //                  + ($investmentSummary->received_total ?? 0)
    //                  + ($rentalSummary->received_total ?? 0);

    //     $totalExpense = ($currencySummary->payment_total ?? 0)
    //                   + ($incomeSummary->payment_total ?? 0)
    //                   + ($investmentSummary->payment_total ?? 0)
    //                   + ($rentalSummary->payment_total ?? 0);

    //     $netProfit = $totalIncome - $totalExpense;

    //     $totalNetReceivable = ($currencySummary->sell_total ?? 0 - $currencySummary->received_total ?? 0)
    //                         + ($investmentSummary->payment_total ?? 0 - $investmentSummary->received_total ?? 0)
    //                         + ($rentExpected ?? 0 - $rentalSummary->received_total ?? 0)
    //                         + ($loansGivenTotal ?? 0 - $loansReceivedTotal ?? 0);

    //     $totalNetPayable = ($currencySummary->buy_total ?? 0 - $currencySummary->payment_total ?? 0)
    //                      + ($investmentSummary->received_total ?? 0 - $investmentSummary->payment_total ?? 0)
    //                      + ($rentSecurity ?? 0)
    //                      + ($loansTakenTotal ?? 0 - $loansPaymentTotal ?? 0);

    //     $response = [
    //         'summary' => [
    //             'total_income_bdt' => $totalIncome,
    //             'total_expense_bdt' => $totalExpense,
    //             'net_profit_bdt' => $netProfit,
    //             'total_net_receivable_bdt' => $totalNetReceivable,
    //             'total_net_payable_bdt' => $totalNetPayable
    //         ]
    //     ];

    //     // Only include month_range if we're filtering by months
    //     if ($months && is_numeric($months) && $months > 0) {
    //         $response['month_range'] = [
    //             'months' => (int)$months,
    //             'start_date' => $startDate->format('Y-m-d'),
    //             'end_date' => $endDate->format('Y-m-d'),
    //             'description' => "Last {$months} months (" . $startDate->format('M Y') . " to " . $endDate->format('M Y') . ")"
    //         ];
    //     } else {
    //         $response['month_range'] = [
    //             'description' => "All Time"
    //         ];
    //     }

    //     return response()->json($response);
    // }

    public function financialSummaryDashboard(Request $request)
    {
        $months = $request->input('months');
        $startDate = null;
        $endDate = null;

        if ($months && is_numeric($months) && $months > 0) {
            $startDate = now()->subMonths($months - 1)->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $dateFilter = function ($query, $column = 'posting_date') use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween($column, [$startDate, $endDate]);
            }
        };

        // Get monthly data for each module
        $monthlyData = [];

        // Currency monthly data
        $currencyMonthly = DB::table('currency_postings')
            ->where('status', 'approved')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('posting_date', [$startDate, $endDate]);
            })
            ->selectRaw("
                DATE_FORMAT(posting_date, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as currency_received,
                SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as currency_payment,
                SUM(CASE WHEN transaction_type='sell' THEN amount_bdt ELSE 0 END) as currency_sell,
                SUM(CASE WHEN transaction_type='buy' THEN amount_bdt ELSE 0 END) as currency_buy
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Income/Expense monthly data
        $incomeExpenseMonthly = DB::table('income_expense_postings')
            ->where('status', 'approved')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('posting_date', [$startDate, $endDate]);
            })
            ->selectRaw("
                DATE_FORMAT(posting_date, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as income_received,
                SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as expense_payment
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Investment monthly data
        $investmentMonthly = DB::table('investment_postings')
            ->where('status', 'approved')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('posting_date', [$startDate, $endDate]);
            })
            ->selectRaw("
                DATE_FORMAT(posting_date, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as investment_received,
                SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as investment_payment
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Rental monthly data
        $rentalMonthly = DB::table('rental_postings')
            ->where('status', 'approved')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('posting_date', [$startDate, $endDate]);
            })
            ->selectRaw("
                DATE_FORMAT(posting_date, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as rental_received,
                SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as rental_payment
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Combine all monthly data
        $allMonths = collect();
        
        // Collect all unique months from all data sources
        $allMonths = $allMonths->merge($currencyMonthly->pluck('month'));
        $allMonths = $allMonths->merge($incomeExpenseMonthly->pluck('month'));
        $allMonths = $allMonths->merge($investmentMonthly->pluck('month'));
        $allMonths = $allMonths->merge($rentalMonthly->pluck('month'));
        
        $uniqueMonths = $allMonths->unique()->sort()->values();

        // Build monthly summary
        $monthlySummary = [];
        foreach ($uniqueMonths as $month) {
            $currency = $currencyMonthly->where('month', $month)->first();
            $incomeExpense = $incomeExpenseMonthly->where('month', $month)->first();
            $investment = $investmentMonthly->where('month', $month)->first();
            $rental = $rentalMonthly->where('month', $month)->first();

            $monthlyIncome = ($currency->currency_received ?? 0) 
                        + ($incomeExpense->income_received ?? 0)
                        + ($investment->investment_received ?? 0)
                        + ($rental->rental_received ?? 0);

            $monthlyExpense = ($currency->currency_payment ?? 0)
                            + ($incomeExpense->expense_payment ?? 0)
                            + ($investment->investment_payment ?? 0)
                            + ($rental->rental_payment ?? 0);

            // Calculate monthly payable and receivable (simplified logic)
            $monthlyPayable = max(0, ($currency->currency_buy ?? 0) - ($currency->currency_payment ?? 0));
            $monthlyReceivable = max(0, ($currency->currency_sell ?? 0) - ($currency->currency_received ?? 0));

            $monthName = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');

            $monthlySummary[] = [
                'month' => $monthName,
                'month_key' => $month,
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
                'payable' => $monthlyPayable,
                'receivable' => $monthlyReceivable,
            ];
        }

        // Your existing total calculations - ADD THE MISSING VARIABLES
        $currencyQuery = DB::table('currency_postings')->where('status', 'approved');
        $dateFilter($currencyQuery);
        $currencySummary = $currencyQuery->selectRaw("
            SUM(CASE WHEN transaction_type='buy' THEN amount_bdt ELSE 0 END) as buy_total,
            SUM(CASE WHEN transaction_type='sell' THEN amount_bdt ELSE 0 END) as sell_total,
            SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
            SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
        ")->first();

        // ADD THE MISSING VARIABLE DEFINITIONS
        $incomeQuery = DB::table('income_expense_postings')->where('status', 'approved');
        $dateFilter($incomeQuery);
        $incomeSummary = $incomeQuery->selectRaw("
            SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
            SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
        ")->first();

        $investmentQuery = DB::table('investment_postings')->where('status', 'approved');
        $dateFilter($investmentQuery);
        $investmentSummary = $investmentQuery->selectRaw("
            SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
            SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
        ")->first();

        $rentalQuery = DB::table('rental_postings')->where('status', 'approved');
        $dateFilter($rentalQuery);
        $rentalSummary = $rentalQuery->selectRaw("
            SUM(CASE WHEN transaction_type='payment' THEN amount_bdt ELSE 0 END) as payment_total,
            SUM(CASE WHEN transaction_type='received' THEN amount_bdt ELSE 0 END) as received_total
        ")->first();

        $rentExpectedQuery = DB::table('rental_mappings')->where('status', 'active');
        $rentExpected = $rentExpectedQuery->selectRaw("
            SUM(
                CASE 
                    WHEN rent_start_date IS NOT NULL AND monthly_rent > 0 THEN 
                        ((YEAR(CURDATE()) - YEAR(STR_TO_DATE(CONCAT(rent_start_date, '-01'), '%Y-%m-%d'))) * 12
                        + (MONTH(CURDATE()) - MONTH(STR_TO_DATE(CONCAT(rent_start_date, '-01'), '%Y-%m-%d'))) + 1)
                        * monthly_rent
                    ELSE 0
                END
            ) as total
        ")->value('total');

        $rentSecurity = DB::table('rental_mappings')->where('status', 'active')->sum('remaining_security_money');

        $loansGiven = DB::table('loans as l')
            ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
            ->join('loan_interest_rates as lr', 'lp.interest_rate_id', '=', 'lr.id')
            ->where('lp.entry_type', 'loan_given')
            ->where('lp.status', 'approved');
        $dateFilter($loansGiven, 'lp.posting_date');
        $loansGivenTotal = $loansGiven->selectRaw("SUM(l.principal_amount + (l.principal_amount*lr.interest_rate/100)) as total")->value('total');

        $loansReceived = DB::table('loans as l')
            ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
            ->where('lp.entry_type', 'loan_received')
            ->where('lp.status', 'approved');
        $dateFilter($loansReceived, 'lp.posting_date');
        $loansReceivedTotal = $loansReceived->sum('lp.amount_bdt');

        $loansTaken = DB::table('loans as l')
            ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
            ->join('loan_interest_rates as lr', 'lp.interest_rate_id', '=', 'lr.id')
            ->where('lp.entry_type', 'loan_taken')
            ->where('lp.status', 'approved');
        $dateFilter($loansTaken, 'lp.posting_date');
        $loansTakenTotal = $loansTaken->selectRaw("SUM(l.principal_amount + (l.principal_amount*lr.interest_rate/100)) as total")->value('total');

        $loansPayment = DB::table('loans as l')
            ->join('loan_postings as lp', 'l.id', '=', 'lp.loan_id')
            ->where('lp.entry_type', 'loan_payment')
            ->where('lp.status', 'approved');
        $dateFilter($loansPayment, 'lp.posting_date');
        $loansPaymentTotal = $loansPayment->sum('lp.amount_bdt');

        $totalIncome = ($currencySummary->received_total ?? 0) 
                    + ($incomeSummary->received_total ?? 0)
                    + ($investmentSummary->received_total ?? 0)
                    + ($rentalSummary->received_total ?? 0);

        $totalExpense = ($currencySummary->payment_total ?? 0)
                    + ($incomeSummary->payment_total ?? 0)
                    + ($investmentSummary->payment_total ?? 0)
                    + ($rentalSummary->payment_total ?? 0);

        $netProfit = $totalIncome - $totalExpense;

        $totalNetReceivable = ($currencySummary->sell_total ?? 0 - $currencySummary->received_total ?? 0)
                            + ($investmentSummary->payment_total ?? 0 - $investmentSummary->received_total ?? 0)
                            + ($rentExpected ?? 0 - $rentalSummary->received_total ?? 0)
                            + ($loansGivenTotal ?? 0 - $loansReceivedTotal ?? 0);

        $totalNetPayable = ($currencySummary->buy_total ?? 0 - $currencySummary->payment_total ?? 0)
                        + ($investmentSummary->received_total ?? 0 - $investmentSummary->payment_total ?? 0)
                        + ($rentSecurity ?? 0)
                        + ($loansTakenTotal ?? 0 - $loansPaymentTotal ?? 0);

        $response = [
            'summary' => [
                'total_income_bdt' => $totalIncome,
                'total_expense_bdt' => $totalExpense,
                'net_profit_bdt' => $netProfit,
                'total_net_receivable_bdt' => $totalNetReceivable,
                'total_net_payable_bdt' => $totalNetPayable
            ],
            'monthly_data' => $monthlySummary
        ];

        // Only include month_range if we're filtering by months
        if ($months && is_numeric($months) && $months > 0) {
            $response['month_range'] = [
                'months' => (int)$months,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'description' => "Last {$months} months (" . $startDate->format('M Y') . " to " . $endDate->format('M Y') . ")"
            ];
        } else {
            $response['month_range'] = [
                'description' => "All Time"
            ];
        }

        return response()->json($response);
    }



    // public function getAccountBalance()
    // {

    //     $source = DB::select("SELECT 
    //     p.method_name,
    //     CASE 
    //         WHEN LOWER(p.method_name) = 'cash' AND LOWER(a.ac_name) = 'cash'
    //             THEN '--'
    //         ELSE CONCAT(a.ac_no, ' - ', a.ac_name)
    //     END AS account_info,
    //     b.balance
    //     FROM account_current_balances b
    //     LEFT JOIN account_numbers a ON a.id = b.account_id
    //     LEFT JOIN payment_channel_details p ON p.id = a.channel_detail_id

    //     UNION ALL

    //     SELECT 
    //         'TOTAL' AS method_name,
    //         '' AS account_info,
    //         SUM(b.balance) AS balance
    //     FROM account_current_balances b
    //     LEFT JOIN account_numbers a ON a.id = b.account_id
    //     LEFT JOIN payment_channel_details p ON p.id = a.channel_detail_id");


    //     return response()->json([
    //         'status' => true,
    //         'data' =>$source,
    //     ], 200);
    // }


    // public function getAccountBalance()
    // {

    //     $source = DB::select("SELECT 
    //         p.method_name,
    //         CASE 
    //             WHEN LOWER(p.method_name) = 'cash' AND LOWER(an.ac_name) = 'cash' 
    //                 THEN '--'
    //             ELSE CONCAT(an.ac_no, ' - ', an.ac_name)
    //         END AS account_info,
    //         (
    //             SUM(CASE WHEN all_posts.transaction_type = 'received' THEN all_posts.amount_bdt ELSE 0 END)
    //             -
    //             SUM(CASE WHEN all_posts.transaction_type = 'payment' THEN all_posts.amount_bdt ELSE 0 END)
    //         ) AS balance
    //     FROM account_numbers AS an
    //     LEFT JOIN payment_channel_details p ON p.id = an.channel_detail_id
    //     LEFT JOIN (
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM rental_postings
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM loan_postings
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM investment_postings
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM currency_postings
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM income_expense_postings
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM transfers
    //         UNION ALL
    //         SELECT account_id, transaction_type, amount_bdt
    //         FROM bank_deposits
    //     ) AS all_posts ON an.id = all_posts.account_id
    //     GROUP BY p.method_name, an.id, an.ac_no, an.ac_name

    //     UNION ALL

    //     SELECT 
    //         'TOTAL' AS method_name,
    //         '' AS account_info,
    //         SUM(
    //             CASE 
    //                 WHEN transaction_type = 'received' THEN amount_bdt
    //                 WHEN transaction_type = 'payment' THEN -amount_bdt
    //                 ELSE 0
    //             END
    //         ) AS balance
    //     FROM (
    //         SELECT transaction_type, amount_bdt FROM rental_postings
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM loan_postings
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM investment_postings
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM currency_postings
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM income_expense_postings
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM transfers
    //         UNION ALL
    //         SELECT transaction_type, amount_bdt FROM bank_deposits
    //     ) AS all_posts_final");


    //     return response()->json([
    //         'status' => true,
    //         'data' =>$source,
    //     ], 200);
    // }


    public function getAccountBalance()
    {

        $source = DB::select("SELECT 
                    p.method_name,
                    CASE 
                        WHEN LOWER(p.method_name) = 'cash' AND LOWER(an.ac_name) = 'cash'  || LOWER(p.method_name) = 'wallet' AND LOWER(an.ac_name) = 'wallet'
                            THEN '--'
                        ELSE CONCAT(an.ac_no, ' - ', an.ac_name)
                    END AS account_info,
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
                GROUP BY 
                    p.method_name,
                    an.id,
                    an.ac_no,
                    an.ac_name

                UNION ALL

                SELECT 
                    'TOTAL' AS method_name,
                    '' AS account_info,
                    SUM(
                        CASE 
                            WHEN transaction_type = 'received' THEN amount_bdt
                            WHEN transaction_type = 'payment' THEN -amount_bdt
                            ELSE 0
                        END
                    ) AS balance
                FROM (

                    SELECT transaction_type, amount_bdt
                    FROM rental_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM loan_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM investment_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM currency_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM income_expense_postings
                    WHERE status = 'approved'

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM transfers
                    WHERE transaction_type IN ('payment','received')

                    UNION ALL
                    SELECT transaction_type, amount_bdt
                    FROM bank_deposits
                    WHERE status = 'approved'

                ) AS all_posts_final");


        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }

  
    
   public function getTotalIncomeExpense(Request $request)
    {
        $filter = $request->input('filter', 6); 

        $whereClause = "WHERE status ='approved'";

        if ($filter !== 'all') {
            $whereClause .= " AND posting_date >= DATE_SUB(CURDATE(), INTERVAL $filter MONTH)";
        }

        $source = DB::select("SELECT
                SUM(CASE WHEN transaction_type = 'received' THEN amount_bdt ELSE 0 END) AS total_received,
                SUM(CASE WHEN transaction_type = 'payment' THEN amount_bdt ELSE 0 END) AS total_payment,
                SUM(CASE WHEN transaction_type = 'received' THEN amount_bdt ELSE 0 END) -
                SUM(CASE WHEN transaction_type = 'payment' THEN amount_bdt ELSE 0 END) AS available_balance
                FROM income_management_db.income_expense_postings
                $whereClause ");


        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }

    public function getTotalIncomeExpenseGraph(Request $request)
    {
        $filter = $request->input('filter', 6); 

        $whereClause = "WHERE status ='approved'";

        if ($filter !== 'all') {
            $whereClause .= " AND posting_date >= DATE_SUB(CURDATE(), INTERVAL $filter MONTH)";
        }

        $source = DB::select("SELECT
            DATE_FORMAT(posting_date, '%b-%Y') AS month,
            SUM(CASE WHEN transaction_type = 'received' THEN amount_bdt ELSE 0 END) AS total_received,
            SUM(CASE WHEN transaction_type = 'payment' THEN amount_bdt ELSE 0 END) AS total_payment
            FROM income_management_db.income_expense_postings
            $whereClause
            GROUP BY month
            ORDER BY month DESC");

        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }

    public function getTotalRental()
    {

        $source = DB::select("SELECT 
        COALESCE(SUM(rhpm.monthly_rent), 0) AS total_monthly_rent,
        COALESCE(SUM(rhpm.security_money), 0) AS total_security_money,
        COALESCE(SUM(rhpm.refund_security_money), 0) AS total_refund_security_money,
        COALESCE((
            SELECT SUM(rp.amount_bdt)
            FROM rental_postings rp
            WHERE rp.entry_type = 'auto_adjustment'
            AND rp.house_id IN (
                SELECT rental_house_id
                FROM rental_house_party_maps
                WHERE status = 'active'
            )
        ), 0) AS total_remaining_security_money
        FROM rental_mappings rhpm
        WHERE rhpm.status = 'active'");


        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }

    public function getMontlyRentalGraph(Request $request)
    {
        $filter = $request->input('filter', 6); 

        $whereClause = "WHERE rp.status ='approved'";

        if ($filter !== 'all') {
            $whereClause .= " AND rp.posting_date >= DATE_SUB(CURDATE(), INTERVAL $filter MONTH)";
        }

        $source = DB::select("SELECT 
            DATE_FORMAT(rp.posting_date, '%b-%Y') AS month,
            COALESCE(SUM(CASE WHEN rp.entry_type = 'rent_received' THEN rp.amount_bdt ELSE 0 END), 0) AS total_received,
            COALESCE(SUM(rhpm.monthly_rent), 0) AS total_receivable,
            COALESCE(SUM(rhpm.monthly_rent), 0) - COALESCE(SUM(CASE WHEN rp.entry_type = 'rent_received' THEN rp.amount_bdt ELSE 0 END), 0) AS total_due
        FROM rental_postings rp
        JOIN rental_mappings rhpm 
            ON rp.house_id = rhpm.house_id
        $whereClause
        AND rhpm.status = 'active'
        AND rp.posting_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(rp.posting_date, '%b-%Y')
        ORDER BY month DESC");

        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }
    
    // public function getTotalLoan()
    // {

    //     $source = DB::select("SELECT
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN principal_amount_num ELSE 0 END)) AS loan_taken_principal,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN paid_amount_num ELSE 0 END)) AS loan_taken_paid,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN emi_due_amount_num ELSE 0 END)) AS loan_taken_emi_due,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN payable_receivable_num ELSE 0 END)) AS loan_taken_payable_receivable,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN principal_amount_num ELSE 0 END)) AS loan_given_principal,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN paid_amount_num ELSE 0 END)) AS loan_given_paid,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN emi_due_amount_num ELSE 0 END)) AS loan_given_emi_due,
    //     ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN payable_receivable_num ELSE 0 END)) AS loan_given_payable_receivable
    //     FROM (
    //         SELECT
    //             CASE 
    //                 WHEN lp.entry_type = 'loan_given' THEN 'Loan Given'
    //                 WHEN lp.entry_type = 'loan_taken' THEN 'Loan Taken'
    //                 ELSE lp.entry_type
    //             END AS Trx_Category,
                
    //             l.principal_amount AS principal_amount_num,
                
    //             CASE 
    //                 WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
    //                 WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
    //             END AS paid_amount_num,
                
                
    //             CASE
    //                 WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
    //                     (
    //                         CASE WHEN lr.interest_rate > 0 THEN
    //                             ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
    //                             / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
    //                         ELSE 0 END
    //                     ) * GREATEST(
    //                             TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1 -
    //                             (CASE
    //                                 WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
    //                                 WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
    //                                 ELSE 0
    //                             END),
    //                         0
    //                     )
    //                 ELSE 0
    //             END AS emi_due_amount_num,

                
    //             CASE 
    //                 WHEN lp.entry_type = 'loan_taken' THEN 
    //                     (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) - COALESCE(agg.sum_payment, 0)
    //                 WHEN lp.entry_type = 'loan_given' THEN 
    //                     (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) - COALESCE(agg.sum_received, 0)
    //                 ELSE 0
    //             END AS payable_receivable_num

    //         FROM loans l
    //         JOIN loan_postings lp
    //             ON l.id = lp.loan_id
    //             AND lp.entry_type IN ('loan_given', 'loan_taken')
    //         JOIN loan_interest_rates lr
    //             ON lp.interest_rate_id = lr.id
    //         JOIN loan_bank_parties lb
    //             ON lp.head_id = lb.id
    //         LEFT JOIN (
    //             SELECT
    //                 loan_id,
    //                 SUM(CASE WHEN entry_type = 'loan_received' THEN amount_bdt ELSE 0 END) AS sum_received,
    //                 SUM(CASE WHEN entry_type = 'loan_payment' THEN amount_bdt ELSE 0 END) AS sum_payment,
    //                 COUNT(CASE WHEN entry_type = 'loan_received' THEN 1 END) AS cnt_received,
    //                 COUNT(CASE WHEN entry_type = 'loan_payment' THEN 1 END) AS cnt_payment,
    //                 MAX(CASE WHEN entry_type = 'loan_received' THEN posting_date END) AS last_received_date,
    //                 MAX(CASE WHEN entry_type = 'loan_payment' THEN posting_date END) AS last_payment_date
    //             FROM loan_postings
    //             GROUP BY loan_id
    //         ) agg
    //             ON agg.loan_id = l.id
    //         WHERE lp.status = 'approved'
    //     ) AS sub");


    //     return response()->json([
    //         'status' => true,
    //         'data' =>$source,
    //     ], 200);
    // }


    public function getTotalLoan()
    {
        $source = DB::select("SELECT
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN principal_amount_num ELSE 0 END)) AS loan_taken_principal,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN paid_amount_num ELSE 0 END)) AS loan_taken_paid,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN emi_due_amount_num ELSE 0 END)) AS loan_taken_emi_due,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN payable_receivable_num ELSE 0 END)) AS loan_taken_payable_receivable,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN principal_amount_num ELSE 0 END)) AS loan_given_principal,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN paid_amount_num ELSE 0 END)) AS loan_given_paid,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN emi_due_amount_num ELSE 0 END)) AS loan_given_emi_due,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN payable_receivable_num ELSE 0 END)) AS loan_given_payable_receivable,
            
            -- Add payable_emi and receivable_emi calculations
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Taken' THEN emi_amount_num ELSE 0 END)) AS payable_emi,
            ROUND(SUM(CASE WHEN Trx_Category = 'Loan Given' THEN emi_amount_num ELSE 0 END)) AS receivable_emi

            FROM (
                SELECT
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN 'Loan Given'
                        WHEN lp.entry_type = 'loan_taken' THEN 'Loan Taken'
                        ELSE lp.entry_type
                    END AS Trx_Category,
                    
                    l.principal_amount AS principal_amount_num,
                    
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.pay_payment, 0)  -- FIXED: changed sum_payment to pay_payment
                    END AS paid_amount_num,
                    
                    -- EMI amount calculation (single EMI)
                    CASE
                        WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                        ELSE 0
                    END AS emi_amount_num,
                    
                    CASE
                        WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                            (
                                CASE WHEN lr.interest_rate > 0 THEN
                                    ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                    / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                ELSE 0 END
                            ) * GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1 -
                                    (CASE
                                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                                        ELSE 0
                                    END),
                                0
                            )
                        ELSE 0
                    END AS emi_due_amount_num,

                    
                    CASE 
                        WHEN lp.entry_type = 'loan_taken' THEN 
                            (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) - COALESCE(agg.pay_payment, 0)  -- FIXED: changed sum_payment to pay_payment
                        WHEN lp.entry_type = 'loan_given' THEN 
                            (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) - COALESCE(agg.sum_received, 0)
                        ELSE 0
                    END AS payable_receivable_num

                FROM loans l
                JOIN loan_postings lp
                    ON l.id = lp.loan_id
                    AND lp.entry_type IN ('loan_given', 'loan_taken')
                JOIN loan_interest_rates lr
                    ON lp.interest_rate_id = lr.id
                JOIN loan_bank_parties lb
                    ON lp.head_id = lb.id
                LEFT JOIN (
                    SELECT
                        loan_id,
                        SUM(CASE WHEN entry_type = 'loan_received' THEN amount_bdt ELSE 0 END) AS sum_received,
                        SUM(CASE WHEN entry_type = 'loan_payment' THEN amount_bdt ELSE 0 END) AS pay_payment,  -- This is defined as pay_payment
                        COUNT(CASE WHEN entry_type = 'loan_received' THEN 1 END) AS cnt_received,
                        COUNT(CASE WHEN entry_type = 'loan_payment' THEN 1 END) AS cnt_payment,
                        MAX(CASE WHEN entry_type = 'loan_received' THEN posting_date END) AS last_received_date,
                        MAX(CASE WHEN entry_type = 'loan_payment' THEN posting_date END) AS last_payment_date
                    FROM loan_postings
                    GROUP BY loan_id
                ) agg
                    ON agg.loan_id = l.id
                WHERE lp.status = 'approved'
            ) AS sub");


        return response()->json([
            'status' => true,
            'data' => $source,
        ], 200);
    }

    public function getInvestment()
    {

                $source = DB::select("SELECT
            SUM(
                CASE 
                    WHEN entry_type = 'investment' 
                        AND transaction_type = 'payment'
                    THEN amount_bdt
                    ELSE 0
                END
            ) AS principal_amount,
            
            SUM(
                CASE 
                    WHEN entry_type = 'investment_return' 
                        AND transaction_type = 'received'
                    THEN amount_bdt
                    ELSE 0
                END
            ) AS investment_return,
            
            SUM(
                CASE 
                    WHEN entry_type = 'investment_profit' 
                        AND transaction_type = 'received'
                    THEN amount_bdt
                    ELSE 0
                END
            ) AS investment_profit,
            
            SUM(
                CASE 
                    WHEN entry_type = 'investment' 
                        AND transaction_type = 'payment'
                    THEN amount_bdt
                    ELSE 0
                END
            ) - 
            SUM(
                CASE 
                    WHEN entry_type = 'investment_return' 
                        AND transaction_type = 'received'
                    THEN amount_bdt
                    ELSE 0
                END
            ) AS receivable

        FROM income_management_db.investment_postings
        WHERE status = 'approved'");


        return response()->json([
            'status' => true,
            'data' =>$source,
        ], 200);
    }
    




    // public function currencySummaryDashboard()
    // {
    
    //     $summaryData = CurrencyPosting::where('status', 'approved')
    //         ->where('business_type_id', 2)
    //         ->selectRaw("
    //             COALESCE(SUM(CASE WHEN transaction_type = 'buy' THEN amount_bdt ELSE 0 END), 0) AS buy_total,
    //             COALESCE(SUM(CASE WHEN transaction_type = 'sell' THEN amount_bdt ELSE 0 END), 0) AS sell_total,
    //             COALESCE(SUM(CASE WHEN transaction_type = 'payment' THEN amount_bdt ELSE 0 END), 0) AS payment_total,
    //             COALESCE(SUM(CASE WHEN transaction_type = 'received' THEN amount_bdt ELSE 0 END), 0) AS received_total,
    //             COALESCE(SUM(CASE WHEN transaction_type = 'buy' THEN currency_amount ELSE 0 END), 0) AS currency_buy_total,
    //             COALESCE(SUM(CASE WHEN transaction_type = 'sell' THEN currency_amount ELSE 0 END), 0) AS currency_sell_total
    //         ")->first();

        
    //     $pnlQuery = CurrencyPosting::where('status', 'approved')
    //         ->where('business_type_id', 2)
    //         ->select([
    //             'currency_id',
    //             'transaction_type',
    //             DB::raw('SUM(amount_bdt) as total_amount_bdt'),
    //             DB::raw('SUM(currency_amount) as total_currency_amount')
    //         ])
    //         ->groupBy(['currency_id', 'transaction_type'])
    //         ->get();

    //     $totalPnlAmount = 0;

    //     $groupedTransactions = $pnlQuery->groupBy('currency_id');
        
    //     foreach ($groupedTransactions as $currencyId => $group) {
    //         $buyData = $group->where('transaction_type', 'buy')->first();
    //         $sellData = $group->where('transaction_type', 'sell')->first();

    //         if ($buyData || $sellData) {
    //             $totalBuyAmountBdt = $buyData ? $buyData->total_amount_bdt : 0;
    //             $totalBuyCurrencyAmount = $buyData ? $buyData->total_currency_amount : 0;
    //             $totalSellAmountBdt = $sellData ? $sellData->total_amount_bdt : 0;
    //             $totalSellCurrencyAmount = $sellData ? $sellData->total_currency_amount : 0;

    //             $avgBuyRate = ($totalBuyCurrencyAmount != 0)
    //                 ? $totalBuyAmountBdt / $totalBuyCurrencyAmount
    //                 : 0;
    //             $avgSellRate = ($totalSellCurrencyAmount != 0)
    //                 ? $totalSellAmountBdt / $totalSellCurrencyAmount
    //                 : 0;

                
    //             $pnlRate = $avgSellRate - $avgBuyRate;
    //             $pnlAmount = $pnlRate * $totalSellCurrencyAmount;

    //             $totalPnlAmount += $pnlAmount;
    //         }
    //     }

    //     $payable = ($summaryData->buy_total + $summaryData->received_total)
    //         - ($summaryData->sell_total + $summaryData->payment_total);

    //     $balance = ($summaryData->received_total + $summaryData->sell_total)
    //         - ($summaryData->payment_total + $summaryData->buy_total);

    //     return response()->json([
    //         'summary' => [
    //             'buy'      => $summaryData->buy_total,
    //             'sell'     => $summaryData->sell_total,
    //             'payment'  => $summaryData->payment_total,
    //             'received' => $summaryData->received_total,
    //             'payable'  => $payable,
    //             'balance'  => $balance,
    //             'pnl_amount' => round($totalPnlAmount, 2),
    //         ]
    //     ]);
    // }

    public function currencySummaryDashboard(Request $request)
    {
        
        $months = $request->input('months');
        
        
        $baseQuery = CurrencyPosting::where('status', 'approved')
            ->where('business_type_id', 2);

        
        if ($months && is_numeric($months)) {
            $startDate = now()->subMonths($months - 1)->startOfMonth();
            $endDate = now()->endOfMonth();
            
            $baseQuery->whereBetween('posting_date', [$startDate, $endDate]);
        }

        
        $summaryData = $baseQuery->selectRaw("
                COALESCE(SUM(CASE WHEN transaction_type = 'buy' THEN amount_bdt ELSE 0 END), 0) AS buy_total,
                COALESCE(SUM(CASE WHEN transaction_type = 'sell' THEN amount_bdt ELSE 0 END), 0) AS sell_total,
                COALESCE(SUM(CASE WHEN transaction_type = 'payment' THEN amount_bdt ELSE 0 END), 0) AS payment_total,
                COALESCE(SUM(CASE WHEN transaction_type = 'received' THEN amount_bdt ELSE 0 END), 0) AS received_total,
                COALESCE(SUM(CASE WHEN transaction_type = 'buy' THEN currency_amount ELSE 0 END), 0) AS currency_buy_total,
                COALESCE(SUM(CASE WHEN transaction_type = 'sell' THEN currency_amount ELSE 0 END), 0) AS currency_sell_total
            ")->first();

        
        $pnlQuery = CurrencyPosting::where('status', 'approved')
            ->where('business_type_id', 2);

        
        if ($months && is_numeric($months)) {
            $startDate = now()->subMonths($months - 1)->startOfMonth();
            $endDate = now()->endOfMonth();
            
            $pnlQuery->whereBetween('posting_date', [$startDate, $endDate]);
        }

        $pnlData = $pnlQuery->select([
                'currency_id',
                'transaction_type',
                DB::raw('SUM(amount_bdt) as total_amount_bdt'),
                DB::raw('SUM(currency_amount) as total_currency_amount')
            ])
            ->groupBy(['currency_id', 'transaction_type'])
            ->get();

        $totalPnlAmount = 0;

        $groupedTransactions = $pnlData->groupBy('currency_id');
        
        foreach ($groupedTransactions as $currencyId => $group) {
            $buyData = $group->where('transaction_type', 'buy')->first();
            $sellData = $group->where('transaction_type', 'sell')->first();

            if ($buyData || $sellData) {
                $totalBuyAmountBdt = $buyData ? $buyData->total_amount_bdt : 0;
                $totalBuyCurrencyAmount = $buyData ? $buyData->total_currency_amount : 0;
                $totalSellAmountBdt = $sellData ? $sellData->total_amount_bdt : 0;
                $totalSellCurrencyAmount = $sellData ? $sellData->total_currency_amount : 0;

                $avgBuyRate = ($totalBuyCurrencyAmount != 0)
                    ? $totalBuyAmountBdt / $totalBuyCurrencyAmount
                    : 0;
                $avgSellRate = ($totalSellCurrencyAmount != 0)
                    ? $totalSellAmountBdt / $totalSellCurrencyAmount
                    : 0;

                $pnlRate = $avgSellRate - $avgBuyRate;
                $pnlAmount = $pnlRate * $totalSellCurrencyAmount;

                $totalPnlAmount += $pnlAmount;
            }
        }

        $payable = ($summaryData->buy_total - $summaryData->payment_total);

        $receivable = ($summaryData->sell_total - $summaryData->received_total) ;

        $balance = ($summaryData->received_total + $summaryData->sell_total)
            - ($summaryData->payment_total + $summaryData->buy_total);

        
        $response = [
            'summary' => [
                'buy'      => $summaryData->buy_total,
                'sell'     => $summaryData->sell_total,
                'payment'  => $summaryData->payment_total,
                'received' => $summaryData->received_total,
                'payable'  => $payable,
                'receivable' => $receivable,
                'balance'  => $balance,
                'pnl_amount' => round($totalPnlAmount, 2),
            ]
        ];

        
        if ($months && is_numeric($months)) {
            $response['month_range'] = [
                'months' => (int)$months,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'description' => "Last {$months} months (" . 
                                $startDate->format('M Y') . ' to ' . 
                                $endDate->format('M Y') . ')'
            ];
        }

        return response()->json($response);
    }
        
}
