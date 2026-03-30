<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LoanPosting;
use App\Models\AccountCurrentBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Loan;
use App\Models\LoanInterestRate;
use App\Models\LoanSchedule;
use App\Models\RentalMapping;
use App\Models\RentalPosting;
use App\Models\AccountNumber;

class LoanPostingController extends Controller
{
    /**
     * Display a listing of the resource.
     */




    

    // public function getLoanLedgerData(Request $request)
    // {
    //     $filters = $request->query();

    //     // Closure to apply filters
    //     $applyFilters = function ($query) use ($filters) {
    //         $query->where('lp.status', 'approved');

    //         if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
    //             $query->where('entry_type', $filters['filter']['transaction_type']);
    //         }

    //         if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //             $query->where('head_id', $filters['filter']['head_id']);
    //         }

    //         if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //             $query->whereBetween('lp.posting_date', [$filters['filter']['start_date'], $filters['filter']['end_date']]);
    //         }

    //         return $query;
    //     };

    //     // Total count
    //     $total = $applyFilters(DB::table('loan_postings as lp'))->count();


    //     $summaryQuery = "
    //         SELECT
    //             SUM(loan_given) AS loan_given,
    //             SUM(loan_taken) AS loan_taken,
    //             SUM(loan_received) AS loan_received,
    //             SUM(loan_payment) AS loan_payment,
    //             SUM(receivable) AS receivable,
    //             SUM(payable) AS payable,
    //             SUM(total_due_amount) AS emi_due_amount,
    //             -- 1. Sum of EMI for Loan Given (receivable_emi)
    //             (SELECT SUM(emi_amount) 
    //             FROM (
    //                 SELECT 
    //                     l.id,
    //                     CASE 
    //                         WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
    //                             (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
    //                             / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
    //                         ELSE 0
    //                     END AS emi_amount
    //                 FROM loans l
    //                 JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
    //                 JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
    //                 WHERE lp.status = 'approved'
    //                 GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
    //             ) AS given_emi
    //             ) AS receivable_emi,
                
    //             -- 2. Sum of EMI for Loan Taken (payable_emi)
    //             (SELECT SUM(emi_amount) 
    //             FROM (
    //                 SELECT 
    //                     l.id,
    //                     CASE 
    //                         WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
    //                             (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
    //                             / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
    //                         ELSE 0
    //                     END AS emi_amount
    //                 FROM loans l
    //                 JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
    //                 JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
    //                 WHERE lp.status = 'approved'
    //                 GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
    //             ) AS taken_emi
    //             ) AS payable_emi,
                
    //             -- 3. Sum of total_due_amount for Loan Given (receivable_emi_due)
    //             (SELECT SUM(total_due_amount) 
    //             FROM (
    //                 SELECT 
    //                     l.id,
    //                     CASE 
    //                         WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
    //                             (
    //                                 (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
    //                                 / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
    //                             ) *
    //                             GREATEST(
    //                                 TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
    //                                 - COALESCE((
    //                                     SELECT COUNT(*) 
    //                                     FROM loan_postings lp2 
    //                                     WHERE lp2.loan_id = l.id 
    //                                     AND lp2.entry_type = 'loan_received'
    //                                     AND lp2.status = 'approved'
    //                                 ), 0),
    //                                 0
    //                             )
    //                         ELSE 0
    //                     END AS total_due_amount
    //                 FROM loans l
    //                 JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
    //                 JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
    //                 WHERE lp.status = 'approved'
    //                 GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
    //             ) AS given_due
    //             ) AS receivable_emi_due,
                
    //             -- 4. Sum of total_due_amount for Loan Taken (payable_emi_due)
    //             (SELECT SUM(total_due_amount) 
    //             FROM (
    //                 SELECT 
    //                     l.id,
    //                     CASE 
    //                         WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
    //                             (
    //                                 (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
    //                                 / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
    //                             ) *
    //                             GREATEST(
    //                                 TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
    //                                 - COALESCE((
    //                                     SELECT COUNT(*) 
    //                                     FROM loan_postings lp2 
    //                                     WHERE lp2.loan_id = l.id 
    //                                     AND lp2.entry_type = 'loan_payment'
    //                                     AND lp2.status = 'approved'
    //                                 ), 0),
    //                                 0
    //                             )
    //                         ELSE 0
    //                     END AS total_due_amount
    //                 FROM loans l
    //                 JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
    //                 JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
    //                 WHERE lp.status = 'approved'
    //                 GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
    //             ) AS taken_due
    //             ) AS payable_emi_due
    //         FROM (
    //             SELECT
    //                 l.id AS loan_id,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_given,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN l.principal_amount ELSE 0 END) AS loan_taken,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
    //                 - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS receivable,
    //                 SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
    //                 - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS payable,
    //                 -- total_due_amount per loan
    //                 (
    //                     (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
    //                     / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
    //                 ) *
    //                 GREATEST(
    //                     TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
    //                     - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN 1 ELSE 0 END)
    //                     - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN 1 ELSE 0 END),
    //                     0
    //                 ) AS total_due_amount
    //             FROM loans l
    //             JOIN loan_postings lp ON l.id = lp.loan_id
    //             JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
    //             WHERE lp.status = 'approved'
    //             GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
    //         ) AS t
    //         ";

    //         // SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_taken,


    //     // Apply filters
    //     $whereConditions = [];
    //     if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
    //         $transactionType = $filters['filter']['transaction_type'];
    //         $whereConditions[] = "lp.entry_type = '$transactionType'";
    //     }
    //     if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //         $headId = $filters['filter']['head_id'];
    //         $whereConditions[] = "lp.head_id = $headId";
    //     }
    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $startDate = $filters['filter']['start_date'];
    //         $endDate = $filters['filter']['end_date'];
    //         $whereConditions[] = "lp.posting_date BETWEEN '$startDate' AND '$endDate'";
    //     }
    //     // if (!empty($whereConditions)) {
    //     //     $summaryQuery .= " AND " . implode(" AND ", $whereConditions);
    //     // }
    //     if (!empty($whereConditions)) {
    //         $filterSql = implode(' AND ', $whereConditions);
    //         $summaryQuery = str_replace(
    //             "WHERE lp.status = 'approved'",
    //             "WHERE lp.status = 'approved' AND " . $filterSql,
    //             $summaryQuery
    //         );
    //     }


    //     $summary = DB::selectOne($summaryQuery);



    //     // Pagination
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;

    //     $aggSubquery = DB::table('loan_postings')
    //         ->select(
    //             'loan_id',
    //             DB::raw("SUM(CASE WHEN entry_type = 'loan_received' THEN amount_bdt ELSE 0 END) AS sum_received"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'loan_payment' THEN amount_bdt ELSE 0 END) AS sum_payment"),
    //             DB::raw("COUNT(CASE WHEN entry_type = 'loan_received' THEN id END) AS cnt_received"),
    //             DB::raw("COUNT(CASE WHEN entry_type = 'loan_payment' THEN id END) AS cnt_payment"),
    //             DB::raw("MAX(CASE WHEN entry_type = 'loan_received' THEN posting_date END) AS last_received_date"),
    //             DB::raw("MAX(CASE WHEN entry_type = 'loan_payment' THEN posting_date END) AS last_payment_date")
    //         )
    //         ->groupBy('loan_id');

    //     // Detailed query with all calculated fields (your existing code remains the same)
    //     $details = $applyFilters(
    //         DB::table('loan_postings as lp')
    //             ->leftJoin('loan_bank_parties as lbp', 'lbp.id', '=', 'lp.head_id')
    //             ->leftJoin('loans as l', 'l.id', '=', 'lp.loan_id')
    //             ->leftJoin('loan_interest_rates as lir', 'lir.id', '=', 'lp.interest_rate_id')
    //             ->leftJoinSub($aggSubquery, 'agg', function ($join) {
    //                 $join->on('agg.loan_id', '=', 'l.id');
    //             })
    //     )
    //         ->select(
    //             'lp.id',
    //             'lp.transaction_type',
    //             'l.principal_amount',
    //             'l.term_in_month',
    //             'lir.interest_rate',
    //             'lp.entry_type',
    //             'lbp.party_name',
    //             'lp.amount_bdt',
    //             'lp.posting_date',
    //             'lp.note',
    //             'lp.status',
    //             DB::raw("
    //             CASE
    //                 WHEN (lp.entry_type = 'loan_given' OR lp.entry_type = 'loan_taken') 
    //                     AND l.term_in_month > 0 
    //                     AND lir.interest_rate > 0 THEN
    //                     FORMAT(
    //                         ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
    //                         / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 )+ l.extra_charge
    //                     , 2)
    //                 ELSE NULL
    //             END AS emi
    //         "),
    //             DB::raw("
    //         CASE
    //             WHEN lp.entry_type = 'loan_given' THEN agg.last_received_date
    //             WHEN lp.entry_type = 'loan_taken' THEN agg.last_payment_date
    //             ELSE NULL
    //         END AS last_payment_date
    //     "),
    //             DB::raw("
    //         CASE
    //             WHEN lp.entry_type = 'loan_given' OR lp.entry_type = 'loan_taken' THEN
    //                 DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
    //             ELSE NULL
    //         END AS installment_date
    //     "),
    //             DB::raw("
    //             CASE
    //     WHEN (
    //         CASE
    //             WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
    //                 DATE(CONCAT(
    //                     DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
    //                     LPAD(l.installment_date, 2, '0')
    //                 ))
    //             WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
    //                 DATE(CONCAT(
    //                     DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
    //                     LPAD(l.installment_date, 2, '0')
    //                 ))
    //             ELSE
    //                 DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
    //         END
    //     ) > l.loan_start_date
    //     THEN (
    //         CASE
    //             WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
    //                 DATE(CONCAT(
    //                     DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
    //                     LPAD(l.installment_date, 2, '0')
    //                 ))
    //             WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
    //                 DATE(CONCAT(
    //                     DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
    //                     LPAD(l.installment_date, 2, '0')
    //                 ))
    //             ELSE
    //                 DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
    //         END
    //     )
    //     ELSE '--'
    //     END AS next_due_date
    //         "),
    //             DB::raw("
    //                 CASE
    //                     WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_given'
    //                     THEN l.term_in_month - COALESCE(agg.cnt_received, 0)
    //                     WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_taken'
    //                     THEN l.term_in_month - COALESCE(agg.cnt_payment, 0)
    //                     ELSE NULL
    //                 END AS remaining_term
    //             "),
    //             DB::raw("
    //                 CASE
    //         WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
    //             FORMAT(
    //                 (
    //                     CASE
    //                         WHEN lir.interest_rate > 0 THEN
    //                             ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
    //                             / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
    //                         ELSE 0
    //                     END
    //                 ) *
    //                 (
    //                     GREATEST(
    //                         TIMESTAMPDIFF(
    //                             MONTH,
    //                             l.loan_start_date,
    //                             CURDATE()
    //                         ) + 1
    //                         -
    //                         (
    //                             CASE
    //                                 WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
    //                                 WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
    //                                 ELSE 0
    //                             END
    //                         ), 
    //                     0)
    //                 )
    //             , 2)
    //         ELSE NULL
    //     END AS total_due_amount
    //             "),
    //             DB::raw("
    //     CASE 
    //     WHEN l.term_in_month > 0 THEN 
    //         FORMAT(
    //             (
    //                 CASE 
    //                     WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
    //                     WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
    //                     ELSE 0
    //                 END
    //             ) -
    //             (
    //                 CASE
    //                     WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
    //                         GREATEST(
    //                             TIMESTAMPDIFF(
    //                                 MONTH,
    //                                 l.loan_start_date,
    //                                 CURDATE()
    //                             ) + 1,
    //                             0
    //                         )
    //                     ELSE 0
    //                 END
    //                 * (
    //                     CASE
    //                         WHEN l.term_in_month > 0 AND lir.interest_rate > 0
    //                             THEN (
    //                                 ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
    //                                 / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
    //                             )
    //                         ELSE 0
    //                     END
    //                 )
    //             ) 
    //         , 2)
    //     ELSE 0
    //     END AS emi_adjustment_amount
    //     "),
    //             DB::raw("
    //         FORMAT(
    //             l.principal_amount - (
    //                 CASE 
    //                     WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
    //                     WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
    //                 END
    //             ), 2
    //         ) AS `Payable / Receivable`
    //     ")
    //         )
    //         ->orderBy('lp.posting_date', 'DESC')
    //         ->orderBy('lp.id', 'DESC')
    //         ->offset(($page - 1) * $pageSize)
    //         ->limit($pageSize)
    //         ->get();

    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //         'total' => $total,
    //     ]);
    // }




    public function getLoanLedgerData(Request $request)
    {
        $filters = $request->query();

        // Set default to current month if no date filters are provided
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');
        
        // If no date filters are provided, set default to current month
        if (!isset($filters['filter']['start_date']) && !isset($filters['filter']['end_date'])) {
            $filters['filter']['start_date'] = $currentMonthStart;
            $filters['filter']['end_date'] = $currentMonthEnd;
        }

        // Closure to apply filters
        $applyFilters = function ($query) use ($filters, $currentMonthStart, $currentMonthEnd) {
            $query->where('lp.status', 'approved');

            if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
                $query->where('entry_type', $filters['filter']['transaction_type']);
            }

            if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
                $query->where('head_id', $filters['filter']['head_id']);
            }

            if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
                $query->whereBetween('lp.posting_date', [$filters['filter']['start_date'], $filters['filter']['end_date']]);
            }

            return $query;
        };

        // Total count
        $total = $applyFilters(DB::table('loan_postings as lp'))->count();


        $summaryQuery = "
            SELECT
                SUM(loan_given) AS loan_given,
                SUM(loan_taken) AS loan_taken,
                SUM(loan_received) AS loan_received,
                SUM(loan_payment) AS loan_payment,
                SUM(receivable) AS receivable,
                SUM(payable) AS payable,
                SUM(total_due_amount) AS emi_due_amount,
                -- 1. Sum of EMI for Loan Given (receivable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
                                / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    ";

        // Add date filter to EMI subquery if applicable
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= " GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS given_emi
                ) AS receivable_emi,
                
                -- 2. Sum of EMI for Loan Taken (payable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
                                / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    ";
        
        // Add date filter to EMI subquery if applicable
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= " GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS taken_emi
                ) AS payable_emi,
                
                -- 3. Sum of total_due_amount for Loan Given (receivable_emi_due)
                (SELECT SUM(total_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
                                    / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    - COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_received'
                                        AND lp2.status = 'approved'
                                        ";
        
        // Add date filter to payment counting subquery if applicable
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp2.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= "                                ), 0),
                                    0
                                )
                            ELSE 0
                        END AS total_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    ";
        
        // Add date filter to main query for due amount calculation
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= " GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
                ) AS given_due
                ) AS receivable_emi_due,
                
                -- 4. Sum of total_due_amount for Loan Taken (payable_emi_due)
                (SELECT SUM(total_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
                                    / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    - COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_payment'
                                        AND lp2.status = 'approved'
                                        ";
        
        // Add date filter to payment counting subquery if applicable
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp2.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= "                                ), 0),
                                    0
                                )
                            ELSE 0
                        END AS total_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    ";
        
        // Add date filter to main query for due amount calculation
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $summaryQuery .= " AND lp.posting_date BETWEEN '" . $filters['filter']['start_date'] . "' AND '" . $filters['filter']['end_date'] . "'";
        }
        
        $summaryQuery .= " GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
                ) AS taken_due
                ) AS payable_emi_due
            FROM (
                SELECT
                    l.id AS loan_id,
                    SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_given,
                    
                    SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN l.principal_amount ELSE 0 END) AS loan_taken,
                    SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
                    SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,
                    SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                    - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS receivable,
                    SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                    - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS payable,
                    -- total_due_amount per loan
                    (
                        (l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month))
                        / (POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1) + l.extra_charge
                    ) *
                    GREATEST(
                        TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                        - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN 1 ELSE 0 END)
                        - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN 1 ELSE 0 END),
                        0
                    ) AS total_due_amount
                FROM loans l
                JOIN loan_postings lp ON l.id = lp.loan_id
                JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                WHERE lp.status = 'approved'
                ";

        // Apply filters to the main summary query
        $whereConditions = [];
        if (isset($filters['filter']['transaction_type']) && $filters['filter']['transaction_type'] !== 'all') {
            $transactionType = $filters['filter']['transaction_type'];
            $whereConditions[] = "lp.entry_type = '$transactionType'";
        }
        if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
            $headId = $filters['filter']['head_id'];
            $whereConditions[] = "lp.head_id = $headId";
        }
        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $startDate = $filters['filter']['start_date'];
            $endDate = $filters['filter']['end_date'];
            $whereConditions[] = "lp.posting_date BETWEEN '$startDate' AND '$endDate'";
        }
        
        if (!empty($whereConditions)) {
            $summaryQuery .= " AND " . implode(" AND ", $whereConditions);
        }
        
        $summaryQuery .= " GROUP BY l.id, l.principal_amount, l.term_in_month, l.loan_start_date, lr.interest_rate, l.extra_charge
            ) AS t";


        // SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_taken,

        $summary = DB::selectOne($summaryQuery);

        // Pagination
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['pageSize'] ?? 10;

        $aggSubquery = DB::table('loan_postings')
            ->select(
                'loan_id',
                DB::raw("SUM(CASE WHEN entry_type = 'loan_received' THEN amount_bdt ELSE 0 END) AS sum_received"),
                DB::raw("SUM(CASE WHEN entry_type = 'loan_payment' THEN amount_bdt ELSE 0 END) AS sum_payment"),
                DB::raw("COUNT(CASE WHEN entry_type = 'loan_received' THEN id END) AS cnt_received"),
                DB::raw("COUNT(CASE WHEN entry_type = 'loan_payment' THEN id END) AS cnt_payment"),
                DB::raw("MAX(CASE WHEN entry_type = 'loan_received' THEN posting_date END) AS last_received_date"),
                DB::raw("MAX(CASE WHEN entry_type = 'loan_payment' THEN posting_date END) AS last_payment_date")
            )
            ->groupBy('loan_id');

        // Detailed query with all calculated fields
        $details = $applyFilters(
            DB::table('loan_postings as lp')
                ->leftJoin('loan_bank_parties as lbp', 'lbp.id', '=', 'lp.head_id')
                ->leftJoin('loans as l', 'l.id', '=', 'lp.loan_id')
                ->leftJoin('loan_interest_rates as lir', 'lir.id', '=', 'lp.interest_rate_id')
                ->leftJoinSub($aggSubquery, 'agg', function ($join) {
                    $join->on('agg.loan_id', '=', 'l.id');
                })
        )
            ->select(
                'lp.id',
                'lp.transaction_type',
                'l.principal_amount',
                'l.term_in_month',
                'lir.interest_rate',
                'lp.entry_type',
                'lbp.party_name',
                'lp.amount_bdt',
                'lp.posting_date',
                'lp.note',
                'lp.status',
                DB::raw("
                CASE
                    WHEN (lp.entry_type = 'loan_given' OR lp.entry_type = 'loan_taken') 
                        AND l.term_in_month > 0 
                        AND lir.interest_rate > 0 THEN
                        FORMAT(
                            ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
                            / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 )+ l.extra_charge
                        , 2)
                    ELSE NULL
                END AS emi
            "),
                DB::raw("
            CASE
                WHEN lp.entry_type = 'loan_given' THEN agg.last_received_date
                WHEN lp.entry_type = 'loan_taken' THEN agg.last_payment_date
                ELSE NULL
            END AS last_payment_date
        "),
                DB::raw("
            CASE
                WHEN lp.entry_type = 'loan_given' OR lp.entry_type = 'loan_taken' THEN
                    DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
                ELSE NULL
            END AS installment_date
        "),
                DB::raw("
                CASE
        WHEN (
            CASE
                WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                ELSE
                    DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
            END
        ) > l.loan_start_date
        THEN (
            CASE
                WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                ELSE
                    DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
            END
        )
        ELSE '--'
        END AS next_due_date
            "),
                DB::raw("
                    CASE
                        WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_given'
                        THEN l.term_in_month - COALESCE(agg.cnt_received, 0)
                        WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_taken'
                        THEN l.term_in_month - COALESCE(agg.cnt_payment, 0)
                        ELSE NULL
                    END AS remaining_term
                "),
                DB::raw("
                    CASE
            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                FORMAT(
                    (
                        CASE
                            WHEN lir.interest_rate > 0 THEN
                                ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END
                    ) *
                    (
                        GREATEST(
                            TIMESTAMPDIFF(
                                MONTH,
                                l.loan_start_date,
                                CURDATE()
                            ) + 1
                            -
                            (
                                CASE
                                    WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                                    WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                                    ELSE 0
                                END
                            ), 
                        0)
                    )
                , 2)
            ELSE NULL
        END AS total_due_amount
                "),
                DB::raw("
        CASE 
        WHEN l.term_in_month > 0 THEN 
            FORMAT(
                (
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                        ELSE 0
                    END
                ) -
                (
                    CASE
                        WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                            GREATEST(
                                TIMESTAMPDIFF(
                                    MONTH,
                                    l.loan_start_date,
                                    CURDATE()
                                ) + 1,
                                0
                            )
                        ELSE 0
                    END
                    * (
                        CASE
                            WHEN l.term_in_month > 0 AND lir.interest_rate > 0
                                THEN (
                                    ( l.principal_amount * (lir.interest_rate/12/100) * POW(1 + (lir.interest_rate/12/100), l.term_in_month) )
                                    / ( POW(1 + (lir.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                )
                            ELSE 0
                        END
                    )
                ) 
            , 2)
        ELSE 0
        END AS emi_adjustment_amount
        "),
                DB::raw("
            FORMAT(
                l.principal_amount - (
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                    END
                ), 2
            ) AS `Payable / Receivable`
        ")
            )
            ->orderBy('lp.posting_date', 'DESC')
            ->orderBy('lp.id', 'DESC')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return response()->json([
            'summary' => $summary,
            'details' => $details,
            'total' => $total,
            'current_month_start' => $currentMonthStart,
            'current_month_end' => $currentMonthEnd,
        ]);
    }


    



    public function getLoanSummary(Request $request)
    {
        
        $transactionType = $request->input(
            'filter.summary_transaction_type',
            $request->input('filter.transaction_type', 'all')
        );

        
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $offset = ($page - 1) * $pageSize;

        
        $loanQuery = "
        SELECT
         -- l.id AS loan_id,
          CASE 
                WHEN lp.entry_type = 'loan_given' THEN 'Loan Given'
                WHEN lp.entry_type = 'loan_taken' THEN 'Loan Taken'
                ELSE lp.entry_type
          END AS Trx_Category,
          
          lb.party_name,
          FORMAT(l.principal_amount, 2) AS principal_amount,
          l.term_in_month,
          FORMAT(
                CASE 
                    WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                    WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                END, 2
            ) AS paid_amount,
          lr.interest_rate AS 'Interest (%)',
          CASE
            WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_given'
              THEN l.term_in_month - COALESCE(agg.cnt_received, 0)
            WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_taken'
              THEN l.term_in_month - COALESCE(agg.cnt_payment, 0)
            ELSE NULL
          END AS remaining_term,
          CASE
            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                FORMAT(
                    (
                        ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                        / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                    ), 2
                )
            ELSE NULL
            END AS emi,
         -- DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0'))) AS installment_date,
          CASE
            WHEN lp.entry_type = 'loan_given' THEN agg.last_received_date
            WHEN lp.entry_type = 'loan_taken'  THEN agg.last_payment_date
          END AS last_payment_date,
          CASE
        WHEN (
            CASE
                WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                ELSE
                    DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
            END
        ) > l.loan_start_date
        THEN (
            CASE
                WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                    DATE(CONCAT(
                        DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                        LPAD(l.installment_date, 2, '0')
                    ))
                ELSE
                    DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
            END
        )
        ELSE '--'
        END AS next_due_date,
                

                CASE
            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                GREATEST(
                    TIMESTAMPDIFF(
                        MONTH,
                        l.loan_start_date,
                        CURDATE()
                    ) + 1
                    -
                    (
                        CASE
                            WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                            WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                            ELSE 0
                        END
                    ), 
                0)
            ELSE 0
        END AS emi_due_month,

                CASE
            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                FORMAT(
                    (
                        CASE
                            WHEN lr.interest_rate > 0 THEN
                                ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END
                    ) *
                    (
                        GREATEST(
                            TIMESTAMPDIFF(
                                MONTH,
                                l.loan_start_date,
                                CURDATE()
                            ) + 1
                            -
                            (
                                CASE
                                    WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                                    WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                                    ELSE 0
                                END
                            ), 
                        0)
                    )
                , 2)
            ELSE NULL
        END AS emi_due_amount,
                CASE 
            WHEN l.term_in_month > 0 THEN 
                FORMAT(
                    (
                        CASE 
                            WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                            WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                            ELSE 0
                        END
                    ) -
                    (
                        CASE
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                GREATEST(
                                    TIMESTAMPDIFF(
                                        MONTH,
                                        l.loan_start_date,
                                        CURDATE()
                                    ) + 1,
                                    0
                                )
                            ELSE 0
                        END
                        * (
                            CASE
                                WHEN l.term_in_month > 0 AND lr.interest_rate > 0
                                    THEN (
                                        ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                        / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                    )
                                ELSE 0
                            END
                        )
                    )
                , 2)
            ELSE 0
        END AS emi_adjusted_amount
        ,
                FORMAT(
                l.principal_amount - (
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                    END
                ), 2
            ) AS `Payable / Receivable`

                


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
                SUM(CASE WHEN entry_type = 'loan_payment'  THEN amount_bdt ELSE 0 END) AS sum_payment,
                COUNT(CASE WHEN entry_type = 'loan_received' THEN 1 END) AS cnt_received,
                COUNT(CASE WHEN entry_type = 'loan_payment'  THEN 1 END) AS cnt_payment,
                MAX(CASE WHEN entry_type = 'loan_received' THEN posting_date END) AS last_received_date,
                MAX(CASE WHEN entry_type = 'loan_payment'  THEN posting_date END) AS last_payment_date
            FROM loan_postings
            GROUP BY loan_id
            ) agg
            ON agg.loan_id = l.id
            WHERE lp.status = 'approved'
            ";





        // Apply transaction type filter if not 'all'
        if ($transactionType !== 'all') {
            $loanQuery .= " AND lp.entry_type = '" . $transactionType . "'";
        }

        // Count total records for pagination
        $countQuery = "SELECT COUNT(*) as total FROM (" . $loanQuery . ") as counted";
        $totalResult = DB::select($countQuery);
        $totalRecords = $totalResult[0]->total;

        // Add pagination to main query
        $loanQuery .= " ORDER BY l.id LIMIT " . $pageSize . " OFFSET " . $offset;

        $loans = DB::select($loanQuery);

        // $summaryQuery = "
        //     SELECT
        //     SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_given,
        //     SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END) AS loan_taken,
        //     SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
        //     SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,
            
        //     -- Receivable = loan_given - loan_received
        //     SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
        //     - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS receivable,

        //     -- Payable = loan_taken - loan_payment
        //     SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
        //     - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS payable

        //     FROM loans l
        //     JOIN loan_postings lp ON l.id = lp.loan_id
        //     JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
        //     WHERE lp.entry_type IN ('loan_given', 'loan_taken', 'loan_received', 'loan_payment')
        //     AND lp.status = 'approved'
        //     ";



        // $summaryQuery = "
        //     SELECT
        //         SUM(CASE WHEN lp.entry_type = 'loan_given' THEN l.principal_amount ELSE 0 END) AS loan_given,
        //         SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN l.principal_amount ELSE 0 END) AS loan_taken,
        //         SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
        //         SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,

        //         -- Receivable = loan_given - loan_received
        //         SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
        //         - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS receivable,

        //         -- Payable = loan_taken - loan_payment
        //         SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
        //         - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS payable,

        //         -- Correct sum of emi_due_amount
        //         (SELECT SUM(emi_due_amount) 
        //         FROM (
        //             SELECT 
        //                 l.id,
        //                 CASE 
        //                     WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
        //                         (
        //                             CASE 
        //                                 WHEN lr.interest_rate > 0 THEN
        //                                     ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
        //                                     / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
        //                                 ELSE 0
        //                             END
        //                         ) *
        //                         GREATEST(
        //                             TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
        //                             -
        //                             COALESCE((
        //                                 SELECT COUNT(*) 
        //                                 FROM loan_postings lp2 
        //                                 WHERE lp2.loan_id = l.id 
        //                                 AND lp2.entry_type = CASE WHEN lp.entry_type = 'loan_given' THEN 'loan_received' ELSE 'loan_payment' END
        //                             ), 0),
        //                         0)
        //                     ELSE 0
        //                 END AS emi_due_amount
        //             FROM loans l
        //             JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type IN ('loan_given', 'loan_taken')
        //             JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
        //             WHERE lp.status = 'approved'
        //             GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge, l.loan_start_date, lp.entry_type
        //         ) AS per_loan
        //         ) AS emi_due_amount
        //     FROM loans l
        //     JOIN loan_postings lp ON l.id = lp.loan_id
        //     JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
        //     WHERE lp.entry_type IN ('loan_given', 'loan_taken', 'loan_received', 'loan_payment')
        //     AND lp.status = 'approved'
        // ";



       $summaryQuery = "
            SELECT
                SUM(CASE WHEN lp.entry_type = 'loan_given' THEN l.principal_amount ELSE 0 END) AS loan_given,
                SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN l.principal_amount ELSE 0 END) AS loan_taken,
                SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
                SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,

                -- Receivable = loan_given - loan_received
                SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                - SUM(CASE WHEN lp.entry_type = 'loan_received' THEN lp.amount_bdt ELSE 0 END) AS receivable,

                -- Payable = loan_taken - loan_payment
                SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                - SUM(CASE WHEN lp.entry_type = 'loan_payment' THEN lp.amount_bdt ELSE 0 END) AS payable,

                -- 1. Sum of EMI for Loan Given (receivable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS given_loans
                ) AS receivable_emi,

                -- 2. Sum of EMI for Loan Taken (payable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS taken_loans
                ) AS payable_emi,

                -- 3. Sum of EMI due amount for Loan Given (receivable_emi_due)
                (SELECT SUM(emi_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    CASE 
                                        WHEN lr.interest_rate > 0 THEN
                                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                        ELSE 0
                                    END
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    -
                                    COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_received'
                                        AND lp2.status = 'approved'
                                    ), 0),
                                0)
                            ELSE 0
                        END AS emi_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge, l.loan_start_date
                ) AS given_loans_due
                ) AS receivable_emi_due,

                -- 4. Sum of EMI due amount for Loan Taken (payable_emi_due)
                (SELECT SUM(emi_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    CASE 
                                        WHEN lr.interest_rate > 0 THEN
                                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                        ELSE 0
                                    END
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    -
                                    COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_payment'
                                        AND lp2.status = 'approved'
                                    ), 0),
                                0)
                            ELSE 0
                        END AS emi_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge, l.loan_start_date
                ) AS taken_loans_due
                ) AS payable_emi_due

            FROM loans l
            JOIN loan_postings lp ON l.id = lp.loan_id
            JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
            WHERE lp.entry_type IN ('loan_given', 'loan_taken', 'loan_received', 'loan_payment')
            AND lp.status = 'approved'
        ";





        // Apply transaction type filter to summary if not 'all'
        if ($transactionType !== 'all') {
            if ($transactionType === 'loan_given') {
                $summaryQuery .= " AND lp.entry_type IN ('loan_given', 'loan_received')";
            } elseif ($transactionType === 'loan_taken') {
                $summaryQuery .= " AND lp.entry_type IN ('loan_taken', 'loan_payment')";
            }
        }

        $summary = DB::select($summaryQuery);

        return response()->json([
            'status' => true,
            'data' => $loans,
            'total' => $totalRecords,
            'summary' => $summary[0] ?? [
                'loan_given' => 0,
                'loan_taken' => 0,
                'loan_received' => 0,
                'loan_payment' => 0,
                'receivable' => 0,
                'payable' => 0,
            ],
        ]);
    }


    public function getLoanSummaryNew(Request $request)
    {
        
        $defaultStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $defaultEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
       
        $filters = $request->input('filter', []);
        
        $startDate = isset($filters['start_date']) && !empty($filters['start_date']) 
            ? $filters['start_date'] 
            : $defaultStartDate;
        
        $endDate = isset($filters['end_date']) && !empty($filters['end_date']) 
            ? $filters['end_date'] 
            : $defaultEndDate;
        
        
        if ($request->filled('start_date')) {
            $startDate = $request->input('start_date');
        }
        if ($request->filled('end_date')) {
            $endDate = $request->input('end_date');
        }
        
        
        if (!strtotime($startDate) || !strtotime($endDate)) {
            $startDate = $defaultStartDate;
            $endDate = $defaultEndDate;
        }
        
        
        $usingDefaultDates = $startDate === $defaultStartDate && $endDate === $defaultEndDate;

        
        $transactionType = isset($filters['summary_transaction_type']) && $filters['summary_transaction_type'] !== 'all'
            ? $filters['summary_transaction_type']
            : (isset($filters['transaction_type']) && $filters['transaction_type'] !== 'all'
                ? $filters['transaction_type']
                : 'all');

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $offset = ($page - 1) * $pageSize;

        
        $loanQuery = "
        SELECT
            CASE 
                WHEN lp.entry_type = 'loan_given' THEN 'Loan Given'
                WHEN lp.entry_type = 'loan_taken' THEN 'Loan Taken'
                ELSE lp.entry_type
            END AS Trx_Category,
            
            lb.party_name,
            FORMAT(l.principal_amount, 2) AS principal_amount,
            l.term_in_month,
            FORMAT(
                CASE 
                    WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                    WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                END, 2
            ) AS paid_amount,
            lr.interest_rate AS 'Interest (%)',
            CASE
                WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_given'
                THEN l.term_in_month - COALESCE(agg.cnt_received, 0)
                WHEN l.term_in_month > 0 AND lp.entry_type = 'loan_taken'
                THEN l.term_in_month - COALESCE(agg.cnt_payment, 0)
                ELSE NULL
            END AS remaining_term,
            CASE
                WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                    FORMAT(
                        (
                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                        ), 2
                    )
                ELSE NULL
            END AS emi,
            CASE
                WHEN lp.entry_type = 'loan_given' THEN agg.last_received_date
                WHEN lp.entry_type = 'loan_taken' THEN agg.last_payment_date
            END AS last_payment_date,
            CASE
                WHEN (
                    CASE
                        WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                            DATE(CONCAT(
                                DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                                LPAD(l.installment_date, 2, '0')
                            ))
                        WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                            DATE(CONCAT(
                                DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                                LPAD(l.installment_date, 2, '0')
                            ))
                        ELSE
                            DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
                    END
                ) > l.loan_start_date
                THEN (
                    CASE
                        WHEN lp.entry_type = 'loan_given' AND agg.last_received_date IS NOT NULL THEN 
                            DATE(CONCAT(
                                DATE_FORMAT(DATE_ADD(agg.last_received_date, INTERVAL 1 MONTH), '%Y-%m-'),
                                LPAD(l.installment_date, 2, '0')
                            ))
                        WHEN lp.entry_type = 'loan_taken' AND agg.last_payment_date IS NOT NULL THEN 
                            DATE(CONCAT(
                                DATE_FORMAT(DATE_ADD(agg.last_payment_date, INTERVAL 1 MONTH), '%Y-%m-'),
                                LPAD(l.installment_date, 2, '0')
                            ))
                        ELSE
                            DATE(CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m-'), LPAD(l.installment_date, 2, '0')))
                    END
                )
                ELSE '--'
            END AS next_due_date,
            
            CASE
                WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                    GREATEST(
                        TIMESTAMPDIFF(
                            MONTH,
                            l.loan_start_date,
                            CURDATE()
                        ) + 1
                        -
                        (
                            CASE
                                WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                                WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                                ELSE 0
                            END
                        ), 
                    0)
                ELSE 0
            END AS emi_due_month,

            CASE
                WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                    FORMAT(
                        (
                            CASE
                                WHEN lr.interest_rate > 0 THEN
                                    ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                    / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                ELSE 0
                            END
                        ) *
                        (
                            GREATEST(
                                TIMESTAMPDIFF(
                                    MONTH,
                                    l.loan_start_date,
                                    CURDATE()
                                ) + 1
                                -
                                (
                                    CASE
                                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.cnt_received, 0)
                                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.cnt_payment, 0)
                                        ELSE 0
                                    END
                                ), 
                            0)
                        )
                    , 2)
                ELSE NULL
            END AS emi_due_amount,
            
            CASE 
                WHEN l.term_in_month > 0 THEN 
                    FORMAT(
                        (
                            CASE 
                                WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                                WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                                ELSE 0
                            END
                        ) -
                        (
                            CASE
                                WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                    GREATEST(
                                        TIMESTAMPDIFF(
                                            MONTH,
                                            l.loan_start_date,
                                            CURDATE()
                                        ) + 1,
                                        0
                                    )
                                ELSE 0
                            END
                            * (
                                CASE
                                    WHEN l.term_in_month > 0 AND lr.interest_rate > 0
                                    THEN (
                                        ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                        / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                    )
                                    ELSE 0
                                END
                            )
                        )
                    , 2)
                ELSE 0
            END AS emi_adjusted_amount,
            
            FORMAT(
                l.principal_amount - (
                    CASE 
                        WHEN lp.entry_type = 'loan_given' THEN COALESCE(agg.sum_received, 0)
                        WHEN lp.entry_type = 'loan_taken' THEN COALESCE(agg.sum_payment, 0)
                    END
                ), 2
            ) AS `Payable / Receivable`
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
                SUM(CASE WHEN entry_type = 'loan_received' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN amount_bdt ELSE 0 END) AS sum_received,
                SUM(CASE WHEN entry_type = 'loan_payment' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN amount_bdt ELSE 0 END) AS sum_payment,
                COUNT(CASE WHEN entry_type = 'loan_received' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN 1 END) AS cnt_received,
                COUNT(CASE WHEN entry_type = 'loan_payment' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN 1 END) AS cnt_payment,
                MAX(CASE WHEN entry_type = 'loan_received' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN posting_date END) AS last_received_date,
                MAX(CASE WHEN entry_type = 'loan_payment' AND posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN posting_date END) AS last_payment_date
            FROM loan_postings
            WHERE posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            GROUP BY loan_id
        ) agg
        ON agg.loan_id = l.id
        WHERE lp.status = 'approved'
        ";

        // Apply transaction type filter if not 'all'
        if ($transactionType !== 'all') {
            $loanQuery .= " AND lp.entry_type = '" . $transactionType . "'";
        }

        // Count total records for pagination
        $countQuery = "SELECT COUNT(*) as total FROM (" . $loanQuery . ") as counted";
        $totalResult = DB::select($countQuery);
        $totalRecords = $totalResult[0]->total;

        // Add pagination to main query
        $loanQuery .= " ORDER BY l.id LIMIT " . $pageSize . " OFFSET " . $offset;

        $loans = DB::select($loanQuery);

        $summaryQuery = "
            SELECT
                SUM(CASE WHEN lp.entry_type = 'loan_given' THEN l.principal_amount ELSE 0 END) AS loan_given,
                SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN l.principal_amount ELSE 0 END) AS loan_taken,
                SUM(CASE WHEN lp.entry_type = 'loan_received' AND lp.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN lp.amount_bdt ELSE 0 END) AS loan_received,
                SUM(CASE WHEN lp.entry_type = 'loan_payment' AND lp.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN lp.amount_bdt ELSE 0 END) AS loan_payment,

                -- Receivable = loan_given - loan_received
                SUM(CASE WHEN lp.entry_type = 'loan_given' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                - SUM(CASE WHEN lp.entry_type = 'loan_received' AND lp.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN lp.amount_bdt ELSE 0 END) AS receivable,

                -- Payable = loan_taken - loan_payment
                SUM(CASE WHEN lp.entry_type = 'loan_taken' THEN (l.principal_amount + (l.principal_amount * lr.interest_rate / 100)) ELSE 0 END)
                - SUM(CASE WHEN lp.entry_type = 'loan_payment' AND lp.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "' THEN lp.amount_bdt ELSE 0 END) AS payable,

                -- 1. Sum of EMI for Loan Given (receivable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS given_loans
                ) AS receivable_emi,

                -- 2. Sum of EMI for Loan Taken (payable_emi)
                (SELECT SUM(emi_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND lr.interest_rate > 0 THEN
                                ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                            ELSE 0
                        END AS emi_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge
                ) AS taken_loans
                ) AS payable_emi,

                -- 3. Sum of EMI due amount for Loan Given (receivable_emi_due)
                (SELECT SUM(emi_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    CASE 
                                        WHEN lr.interest_rate > 0 THEN
                                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                        ELSE 0
                                    END
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    -
                                    COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_received'
                                        AND lp2.status = 'approved'
                                        AND lp2.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                                    ), 0),
                                0)
                            ELSE 0
                        END AS emi_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_given'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge, l.loan_start_date
                ) AS given_loans_due
                ) AS receivable_emi_due,

                -- 4. Sum of EMI due amount for Loan Taken (payable_emi_due)
                (SELECT SUM(emi_due_amount) 
                FROM (
                    SELECT 
                        l.id,
                        CASE 
                            WHEN l.term_in_month > 0 AND CURDATE() > l.loan_start_date THEN
                                (
                                    CASE 
                                        WHEN lr.interest_rate > 0 THEN
                                            ( l.principal_amount * (lr.interest_rate/12/100) * POW(1 + (lr.interest_rate/12/100), l.term_in_month) )
                                            / ( POW(1 + (lr.interest_rate/12/100), l.term_in_month) - 1 ) + l.extra_charge
                                        ELSE 0
                                    END
                                ) *
                                GREATEST(
                                    TIMESTAMPDIFF(MONTH, l.loan_start_date, CURDATE()) + 1
                                    -
                                    COALESCE((
                                        SELECT COUNT(*) 
                                        FROM loan_postings lp2 
                                        WHERE lp2.loan_id = l.id 
                                        AND lp2.entry_type = 'loan_payment'
                                        AND lp2.status = 'approved'
                                        AND lp2.posting_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                                    ), 0),
                                0)
                            ELSE 0
                        END AS emi_due_amount
                    FROM loans l
                    JOIN loan_postings lp ON l.id = lp.loan_id AND lp.entry_type = 'loan_taken'
                    JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
                    WHERE lp.status = 'approved'
                    GROUP BY l.id, l.principal_amount, l.term_in_month, lr.interest_rate, l.extra_charge, l.loan_start_date
                ) AS taken_loans_due
                ) AS payable_emi_due

            FROM loans l
            JOIN loan_postings lp ON l.id = lp.loan_id
            JOIN loan_interest_rates lr ON lp.interest_rate_id = lr.id
            WHERE lp.entry_type IN ('loan_given', 'loan_taken', 'loan_received', 'loan_payment')
            AND lp.status = 'approved'
        ";

        
        if ($transactionType !== 'all') {
            if ($transactionType === 'loan_given') {
                $summaryQuery .= " AND lp.entry_type IN ('loan_given', 'loan_received')";
            } elseif ($transactionType === 'loan_taken') {
                $summaryQuery .= " AND lp.entry_type IN ('loan_taken', 'loan_payment')";
            }
        }

        $summary = DB::select($summaryQuery);

        return response()->json([
            'status' => true,
            'data' => $loans,
            'total' => $totalRecords,
            'summary' => $summary[0] ?? [
                'loan_given' => 0,
                'loan_taken' => 0,
                'loan_received' => 0,
                'loan_payment' => 0,
                'receivable' => 0,
                'payable' => 0,
            ],
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_default_range' => $usingDefaultDates
            ]
        ]);
    }


    

    public function getLoanCalculation($loan_party_id, $interest_rate_date)
    {
        $loanPosting = LoanPosting::where('head_id', $loan_party_id)
            ->whereIn('entry_type', ['loan_taken', 'loan_given'])
            ->where('status', 'approved')
            ->whereHas('loan', function ($query) {
                $query->where('status', 'active');
            })
            ->with(['loan.interestRates', 'loan.loanPayments'])
            ->first();

        if (!$loanPosting) {
            return response()->json([
                'loan_principal_amount' => 0,
                'loan_principal_amount_with_interest' => 0,
                'remaining_balance' => 0,
                'per_month' => 0,
                'total_term' => 0,
                'remaining_term' => 0,
                'interest_rate' => 0,
                'interest_rate_id' => null,
                'loan_id' => null,
            ]);
        }

        $loan = $loanPosting->loan;

        $principal = $loan->principal_amount;
        $extra_charge = $loan->extra_charge;
        $totalTermMonths = $loan->term_in_month;

        // Payments made
        $paymentsMade = $loan->loanPayments->where('status', 'approved')->sum('amount_bdt');
        $paidTerm = $loan->loanPayments->where('status', 'approved')->count();
        $remainingTerm = $totalTermMonths > 0 ? $totalTermMonths - $paidTerm : $totalTermMonths;

        // Latest interest rate
        $latestInterestRate = $loan->interestRates()
            ->where('effective_date', '<=', now())
            ->orderByDesc('effective_date')
            ->first();

        $annualInterestRate = $latestInterestRate ? (float) $latestInterestRate->interest_rate : 0;
        $interestRateId = $latestInterestRate ? $latestInterestRate->id : null;

        // Monthly interest rate
        $monthlyRate = $annualInterestRate / (12 * 100);

        // EMI calculation
        if ($totalTermMonths > 0) {
            if ($monthlyRate > 0) {
                $emi = ($principal * $monthlyRate *  (pow(1 + $monthlyRate, $totalTermMonths)))
                    / (((pow(1 + $monthlyRate, $totalTermMonths)) - 1));
                $totalPayable = $emi * $totalTermMonths;
                $remainingBalance = $totalPayable - $paymentsMade;
            } else {
                // No interest case
                $emi = $principal / $totalTermMonths;
                $totalPayable = $emi * $totalTermMonths;
                $remainingBalance = $totalPayable - $paymentsMade;
            }
        } else {
            // No term defined
            $emi = 0;
            $totalPayable = $principal;
            $remainingBalance = $totalPayable - $paymentsMade;
        }

        // return $emi = $emi;
        // $totalPayable = $emi * $totalTermMonths;
        // $remainingBalance = $totalPayable - $paymentsMade;

        return response()->json([
            'loan_principal_amount' => $principal,
            'loan_principal_amount_with_interest' => round($totalPayable, 2),
            'total_payments' => $paymentsMade,
            'remaining_balance' => round($remainingBalance, 2),
            'per_month' => round($emi, 2) + ($extra_charge ?? 0),
            'total_term' => $totalTermMonths,
            'remaining_term' => $remainingTerm,
            'interest_rate' => $annualInterestRate,
            'interest_rate_id' => $interestRateId,
            'loan_id' => $loan->id,
        ]);
    }


    // public function index(Request $request)
    // {
    //     $pageSize = $request->input('pageSize', 10);
    //     $status = $request->input('status');

    //     if (empty($status) && $status !== 'all') {
    //         $status = 'pending';
    //     }

    //     $query = DB::table('loan_postings as iep')
    //         ->leftJoin('loan_bank_parties as ih', 'ih.id', '=', 'iep.head_id')
    //         ->join('payment_channel_details as pcd', 'pcd.id', '=', 'iep.payment_channel_id')
    //         ->leftJoin('account_numbers as ac', 'ac.id', '=', 'iep.account_id')
    //         ->leftJoin('loans as l', 'l.id', '=', 'iep.loan_id')
    //         // ->leftJoin('loan_interest_rates as lir', 'lir.id', '=', 'iep.interest_rate_id')
    //         ->leftJoin('loan_interest_rates as lir', function ($join) {
    //             $join->on('lir.loan_id', '=', 'iep.loan_id')
    //                 ->whereRaw('lir.id = (SELECT MAX(id) FROM loan_interest_rates WHERE loan_id = iep.loan_id)');
    //         })
    //         ->select(
    //             'iep.*',
    //             'ih.party_name',
    //             'pcd.method_name',
    //             'ac.ac_no',
    //             'ac.ac_name',
    //             'l.principal_amount',
    //             'l.extra_charge',
    //             'l.term_in_month',
    //             'l.loan_start_date',
    //             'l.status as loan_status',
    //             'lir.interest_rate',
    //             'lir.effective_date',
    //             'lir.end_date'
    //         );


    //     if ($status !== 'all') {
    //         $query->where('iep.status', $status);
    //     }
    //     $query->orderBy('iep.id', 'desc');
    //     $source = $query->paginate($pageSize);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Retrieved successfully.',
    //         'data' => $source->items(),
    //         'total' => $source->total(),
    //         'current_page' => $source->currentPage(),
    //         'last_page' => $source->lastPage(),
    //     ]);
    // }


    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $status = $request->input('status', 'pending');
        $entryType = $request->input('entry_type');

        $query = DB::table('loan_postings as iep')
            ->leftJoin('loan_bank_parties as ih', 'ih.id', '=', 'iep.head_id')
            ->join('payment_channel_details as pcd', 'pcd.id', '=', 'iep.payment_channel_id')
            ->leftJoin('account_numbers as ac', 'ac.id', '=', 'iep.account_id')
            ->leftJoin('loans as l', 'l.id', '=', 'iep.loan_id')
            ->leftJoin('loan_interest_rates as lir', function ($join) {
                $join->on('lir.loan_id', '=', 'iep.loan_id')
                    ->whereRaw('lir.id = (SELECT MAX(id) FROM loan_interest_rates WHERE loan_id = iep.loan_id)');
            })
            ->select(
                'iep.*',
                'ih.party_name',
                'pcd.method_name',
                'ac.ac_no',
                'ac.ac_name',
                'l.principal_amount',
                'l.extra_charge',
                'l.term_in_month',
                'l.loan_start_date',
                'l.status as loan_status',
                'lir.interest_rate',
                'lir.effective_date',
                'lir.end_date'
            );

        if ($status !== 'all') {
            $query->where('iep.status', $status);
        }

        if (!empty($entryType) && $entryType !== 'all') {
            $query->where('iep.entry_type', $entryType);
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

   

    // public function store(Request $request)
    // {
    //     $mapping = [
    //         'loan_taken'   => 'received',
    //         'loan_given'   => 'payment',
    //         'loan_payment' => 'payment',
    //         'loan_received' => 'received',
    //     ];

    //     try {
    //         $posting = null;

    //         DB::transaction(function () use ($request, $mapping, &$posting) {
    //             $loanId = $request->input('loan_id');
    //             $interestRateId = $request->input('interest_rate_id');

    //             if ($request->input('transaction_type') === 'loan_taken' || $request->input('transaction_type') === 'loan_given') {
    //                 $principal    = (float) $request->input('amount_bdt');
    //                 $interestRate = (float) $request->input('interest_rate');
    //                 $termMonths   = (int) $request->input('term_months');
    //                 $installmentDate = (int) $request->input('installment_date');
    //                 $startDate    = Carbon::parse($request->input('posting_date'));
    //                 $interestRateEffectiveDate    = Carbon::parse($request->input('interest_rate_effective_date'));


    //                 $loan = Loan::create([
    //                     'principal_amount' => $request->input('amount_bdt'),
    //                     'extra_charge'     => $request->input('extra_charge') ?? 0,
    //                     'term_in_month'    => $termMonths,
    //                     'loan_start_date'  => $startDate,
    //                     'installment_date' => $installmentDate,
    //                     'status'           => 'active',
    //                 ]);

    //                 $loanId = $loan->id;


    //                 $rate = LoanInterestRate::create([
    //                     'loan_id'        => $loan->id,
    //                     'interest_rate'  => $interestRate,
    //                     'effective_date' => $interestRateEffectiveDate,
    //                     'end_date'       => null,
    //                 ]);

    //                 $interestRateId = $rate->id;
    //             }


    //             $posting = LoanPosting::create([
    //                 'transaction_type'   => $mapping[$request->input('transaction_type')],
    //                 'head_type'          => $request->input('head_type'),
    //                 'head_id'            => $request->input('head_id'),
    //                 'payment_channel_id' => $request->input('payment_channel_id'),
    //                 'account_id'         => $request->input('account_id'),
    //                 'receipt_number'     => $request->input('receipt_number'),
    //                 'amount_bdt'         => $request->input('amount_bdt'),
    //                 'posting_date'       => $request->input('posting_date'),
    //                 'note'               => $request->input('note'),
    //                 'entry_type'         => $request->input('transaction_type'),
    //                 'loan_id'            => $loanId,
    //                 'interest_rate_id'   => $interestRateId,
    //             ]);


    //             $accountId = $request->input('account_id');
    //             $amount = (float) $request->input('amount_bdt');
    //             $transactionType = $mapping[$request->input('transaction_type')];

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
    //             'status'  => true,
    //             'message' => 'Created successfully.',
    //             'data'    => $posting,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Transaction failed. ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }



    public function store(Request $request)
    {
        $mapping = [
            'loan_taken'    => 'received',
            'loan_given'    => 'payment',
            'loan_payment'  => 'payment',
            'loan_received' => 'received',
        ];

        try {
            // --------------------------------------------
            // 1. Validate balance BEFORE starting DB transaction
            // --------------------------------------------
            $accountId        = $request->input('account_id');
            $amount           = (float) $request->input('amount_bdt');
            $transactionType  = $mapping[$request->input('transaction_type')];

            // Only validate balance if money is going OUT
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

            // --------------------------------------------
            // 2. Perform DB Transaction
            // --------------------------------------------
            $posting = null;

            DB::transaction(function () use ($request, $mapping, &$posting) {

                $loanId           = $request->input('loan_id');
                $interestRateId   = $request->input('interest_rate_id');
                $entryType        = $request->input('transaction_type');

                // --------------------------------------------
                // Create loan + interest rate when needed
                // --------------------------------------------
                if (in_array($entryType, ['loan_taken', 'loan_given'])) {

                    $principal       = (float) $request->input('amount_bdt');
                    $interestRate    = (float) $request->input('interest_rate');
                    $termMonths      = (int) $request->input('term_months');
                    $installmentDate = (int) $request->input('installment_date');
                    $startDate       = Carbon::parse($request->input('posting_date'));
                    $rateEffective   = Carbon::parse($request->input('interest_rate_effective_date'));

                    // Create loan record
                    $loan = Loan::create([
                        'principal_amount' => $principal,
                        'extra_charge'     => $request->input('extra_charge') ?? 0,
                        'term_in_month'    => $termMonths,
                        'loan_start_date'  => $startDate,
                        'installment_date' => $installmentDate,
                        'status'           => 'active',
                    ]);

                    $loanId = $loan->id;

                    // Create interest rate record
                    $rate = LoanInterestRate::create([
                        'loan_id'        => $loan->id,
                        'interest_rate'  => $interestRate,
                        'effective_date' => $rateEffective,
                        'end_date'       => null,
                    ]);

                    $interestRateId = $rate->id;
                }
                $posting = LoanPosting::create([
                    'transaction_type'   => $mapping[$entryType],
                    'head_type'          => $request->input('head_type'),
                    'head_id'            => $request->input('head_id'),
                    'payment_channel_id' => $request->input('payment_channel_id'),
                    'account_id'         => $request->input('account_id'),
                    'receipt_number'     => $request->input('receipt_number'),
                    'amount_bdt'         => $request->input('amount_bdt'),
                    'posting_date'       => $request->input('posting_date'),
                    'note'               => $request->input('note'),
                    'entry_type'         => $entryType,
                    'loan_id'            => $loanId,
                    'interest_rate_id'   => $interestRateId,
                ]);

                $accountId       = $request->input('account_id');
                $amount          = (float) $request->input('amount_bdt');
                $transactionType = $mapping[$entryType];

                $currentBalance = AccountCurrentBalance::firstOrCreate(
                    ['account_id' => $accountId],
                    ['balance' => 0]
                );

                if ($transactionType === 'received') {
                    $currentBalance->balance += $amount;
                } else {
                    $currentBalance->balance -= $amount;
                }

                $currentBalance->save();
            });

            return response()->json([
                'status'  => true,
                'message' => 'Created successfully.',
                'data'    => $posting,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction failed. ' . $e->getMessage(),
            ], 500);
        }
    }

  
    public function show(string $id)
    {
        // Find the source by ID
        $source = LoanPosting::find($id);

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

   


    public function update(Request $request, string $id)
    {
        // Find the loan posting record
        $loanPosting = LoanPosting::find($id);

        // Handle case where loan posting is not found
        if (!$loanPosting) {
            return response()->json([
                'status' => false,
                'message' => 'Loan posting not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Store old values for calculation
            $oldAmount = (float) $loanPosting->amount_bdt;
            $oldTransactionType = $loanPosting->transaction_type;
            $accountId = $loanPosting->account_id;

            $newRate = null;

            // Check if the request contains new interest rate data
            if ($request->has('interest_rate') && $request->has('interest_rate_effective_date')) {
                $loanId = $loanPosting->loan_id;
                $newInterestRate = $request->input('interest_rate');
                $effectiveDate = $request->input('interest_rate_effective_date');

                // Find the active interest rate record for this loan
                $previousRate = LoanInterestRate::where('loan_id', $loanId)
                    ->whereNull('end_date')
                    ->first();

                // If a previous active rate exists, update its end_date
                if ($previousRate) {
                    $endDate = Carbon::parse($effectiveDate)->subDay()->toDateString();
                    $previousRate->update(['end_date' => $endDate]);
                }

                // Insert the new interest rate record
                $newRate = LoanInterestRate::create([
                    'loan_id' => $loanId,
                    'interest_rate' => $newInterestRate,
                    'effective_date' => $effectiveDate,
                    'end_date' => null,
                ]);
            }

            // Update the LoanPosting
            $loanPosting->update($request->all());

            // Get new values
            $newAmount = (float) $request->input('amount_bdt', $loanPosting->amount_bdt);
            $newTransactionType = $request->input('transaction_type', $loanPosting->transaction_type);

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
                'status' => true,
                'message' => 'Updated successfully.',
                'data' => $loanPosting,
                'new_interest_rate_data' => $newRate,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred during the update.',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        $source = LoanPosting::find($id);

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
        $posting = LoanPosting::find($id);

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
        $posting = LoanPosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Loan posting not found.'
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
        $posting = LoanPosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Loan posting not found.'
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
