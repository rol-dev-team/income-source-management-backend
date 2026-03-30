<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RentalPosting;
use App\Models\RentalParty;
use App\Models\RentalHousePartyMap;
use App\Models\AccountCurrentBalance;
use App\Models\RentalHouse;
use App\Models\RentalMapping;
use Carbon\Carbon;

class RentalPostingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

   

    // public function getRentalLedgerData(Request $request)
    // {
    //     $filters = $request->query('filter', []);
    //     $houseId = $filters['house_id'] ?? '';
    //     $headId = $filters['head_id'] ?? '';
    //     $startDate = $filters['start_date'] ?? '';
    //     $endDate = $filters['end_date'] ?? '';
    //     $transactionType = $filters['transaction_type'] ?? 'all';

    //     // --- Change for Default Current Month Filter ---
    //     // If startDate and endDate are empty, set them to the current month's range
    //     if (empty($startDate) && empty($endDate)) {
    //         $currentDate = \Carbon\Carbon::now();
    //         $startDate = $currentDate->startOfMonth()->toDateString(); // YYYY-MM-01
    //         $endDate = $currentDate->endOfMonth()->toDateString();   // YYYY-MM-last_day
    //     }
    //     // ----------------------------------------------

    //     $page = $request->query('page', 1);
    //     $pageSize = $request->query('pageSize', 10);
    //     $rentalMappingsQuery = RentalMapping::with(['rentalHouse', 'rentalParty'])
    //     ->where('status', 'active');
    //     if (!empty($headId)) {
    //         $rentalMappingsQuery->where('party_id', $headId);
    //     }

    //     if (!empty($houseId)) {
    //         $rentalMappingsQuery->where('house_id', $houseId);
    //     }

    //     $rentalMappings = $rentalMappingsQuery->get();

        
    //     $partyIdsFromMappings = $rentalMappings->pluck('party_id')->unique()->toArray();

    //     $details = collect();
    //     $total = 0;
    //     $allDetailsForSummary = collect(); // Store all details before pagination [cite: 8]

    //     if (!empty($partyIdsFromMappings)) {
            
    //         $postingsQuery = RentalPosting::with(['rentalParty'])
    //             ->where('status', 'approved')
    //             ->whereIn('head_id', $partyIdsFromMappings);
    //         if ($transactionType !== 'all') {
    //             $postingsQuery->where('entry_type', $transactionType);
    //         }

    //         // Apply the date filter to postings [cite: 10]
    //         if (!empty($startDate) && !empty($endDate)) {
    //             $postingsQuery->whereBetween('posting_date', [$startDate, $endDate]);
    //         }

    //         $postings = $postingsQuery
    //             ->orderBy('posting_date', 'DESC')
    //             ->orderBy('id', 'DESC')
    //             ->get();
    //         $postingDetails = collect();
    //         foreach ($postings as $posting) {
    //             $postingDetails->push($this->formatPostingData($posting, $rentalMappings));
    //         }

            
    //         $syntheticDetails = collect();
    //         foreach ($rentalMappings as $mapping) {
    //             // Check if we should include this mapping based on filters
    //             $shouldInclude = true;
    //             if (!empty($headId) && $mapping->party_id != $headId) {
    //                 $shouldInclude = false;
    //             }
    //             if (!empty($houseId) && $mapping->house_id != $houseId) {
    //                 $shouldInclude = false;
    //             }

    //             if ($shouldInclude) {
    //                 // For each mapping, check if we need to create synthetic entries for different rent periods
    //                 $syntheticEntries = $this->createSyntheticEntriesForMapping($mapping, $postings);
    //                 $syntheticDetails = $syntheticDetails->merge($syntheticEntries);
    //             }
    //         }

            
    //         // This collection contains only the details relevant to the date filter [cite: 18]
    //         $allDetailsForSummary = $postingDetails->merge($syntheticDetails);
    //         // Remove duplicates - if a posting already exists for a mapping, don't show synthetic entry
    //         $allDetailsForSummary = $this->removeDuplicateEntries($allDetailsForSummary);
    //         $total = $allDetailsForSummary->count();

            
    //         $details = $allDetailsForSummary->slice(($page - 1) * $pageSize, $pageSize)->values();
    //     }

        
    //     // The summary is calculated ONLY from the details ($allDetailsForSummary) which have been filtered by date 
    //     $summary = $this->calculateLedgerSummaryFromDetails($allDetailsForSummary, $rentalMappings);
    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //         'total' => $total,
    //     ]);
    // }



    public function getRentalLedgerData(Request $request)
    {
        $filters = $request->query('filter', []);
        $houseId = $filters['house_id'] ?? '';
        $headId = $filters['head_id'] ?? '';
        $startDate = $filters['start_date'] ?? '';
        $endDate = $filters['end_date'] ?? '';
        $transactionType = $filters['transaction_type'] ?? 'all';

        // --- Change for Default Current Month Filter ---
        if (empty($startDate) && empty($endDate)) {
            $currentDate = \Carbon\Carbon::now();
            $startDate = $currentDate->startOfMonth()->toDateString();
            $endDate = $currentDate->endOfMonth()->toDateString();
        }
        // ----------------------------------------------

        $page = $request->query('page', 1);
        $pageSize = $request->query('pageSize', 10);
        $rentalMappingsQuery = RentalMapping::with(['rentalHouse', 'rentalParty'])
            ->where('status', 'active');
        if (!empty($headId)) {
            $rentalMappingsQuery->where('party_id', $headId);
        }

        if (!empty($houseId)) {
            $rentalMappingsQuery->where('house_id', $houseId);
        }

        $rentalMappings = $rentalMappingsQuery->get();

        $partyIdsFromMappings = $rentalMappings->pluck('party_id')->unique()->toArray();

        $details = collect();
        $total = 0;
        $allDetailsForSummary = collect();

        if (!empty($partyIdsFromMappings)) {
            
            $postingsQuery = RentalPosting::with(['rentalParty'])
                ->where('status', 'approved')
                ->whereIn('head_id', $partyIdsFromMappings);
            if ($transactionType !== 'all') {
                $postingsQuery->where('entry_type', $transactionType);
            }

            // Apply the date filter to postings
            if (!empty($startDate) && !empty($endDate)) {
                $postingsQuery->whereBetween('posting_date', [$startDate, $endDate]);
            }

            $postings = $postingsQuery
                ->orderBy('posting_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get();
            
            $postingDetails = collect();
            foreach ($postings as $posting) {
                $postingDetails->push($this->formatPostingData($posting, $rentalMappings));
            }

            $syntheticDetails = collect();
            foreach ($rentalMappings as $mapping) {
                // Check if we should include this mapping based on filters
                $shouldInclude = true;
                if (!empty($headId) && $mapping->party_id != $headId) {
                    $shouldInclude = false;
                }
                if (!empty($houseId) && $mapping->house_id != $houseId) {
                    $shouldInclude = false;
                }

                if ($shouldInclude) {
                    // For each mapping, check if we need to create synthetic entries for different rent periods
                    $syntheticEntries = $this->createSyntheticEntriesForMapping($mapping, $postings);
                    $syntheticDetails = $syntheticDetails->merge($syntheticEntries);
                }
            }

            // This collection contains only the details relevant to the date filter
            $allDetailsForSummary = $postingDetails->merge($syntheticDetails);
            // Remove duplicates - if a posting already exists for a mapping, don't show synthetic entry
            $allDetailsForSummary = $this->removeDuplicateEntries($allDetailsForSummary);
            $total = $allDetailsForSummary->count();

            // The summary is calculated ONLY from the details which have been filtered by date
            $summary = $this->calculateLedgerSummaryFromDetails($allDetailsForSummary, $rentalMappings, $startDate, $endDate);
        }

        $details = $allDetailsForSummary->slice(($page - 1) * $pageSize, $pageSize)->values();

        return response()->json([
            'summary' => $summary,
            'details' => $details,
            'total' => $total,
        ]);
    }


    // for all data together

    // public function getRentalLedgerData(Request $request)
    // {
    //     $filters = $request->query('filter', []);
    //     $houseId = $filters['house_id'] ?? '';
    //     $headId = $filters['head_id'] ?? '';
    //     $startDate = $filters['start_date'] ?? '';
    //     $endDate = $filters['end_date'] ?? '';
    //     $transactionType = $filters['transaction_type'] ?? 'all';

    //     // --- Change: Load ALL data by default (from earliest to today) ---
    //     if (empty($startDate) && empty($endDate)) {
    //         // Get the earliest rent start date from all mappings
    //         $earliestMapping = RentalMapping::where('status', '!=', 'deleted')
    //             ->orderBy('rent_start_date', 'asc')
    //             ->first();
            
    //         if ($earliestMapping && $earliestMapping->rent_start_date) {
    //             $startDate = \Carbon\Carbon::parse($earliestMapping->rent_start_date)->toDateString();
    //         } else {
    //             // Fallback: if no mappings exist, use 1 year ago
    //             $startDate = \Carbon\Carbon::now()->subYear()->toDateString();
    //         }
            
    //         // End date is today
    //         $endDate = \Carbon\Carbon::now()->toDateString();
    //     }
    //     // -------------------------------------------------------

    //     $page = $request->query('page', 1);
    //     $pageSize = $request->query('pageSize', 10);
    //     $rentalMappingsQuery = RentalMapping::with(['rentalHouse', 'rentalParty'])
    //         ->where('status', 'active');
    //     if (!empty($headId)) {
    //         $rentalMappingsQuery->where('party_id', $headId);
    //     }

    //     if (!empty($houseId)) {
    //         $rentalMappingsQuery->where('house_id', $houseId);
    //     }

    //     $rentalMappings = $rentalMappingsQuery->get();

    //     $partyIdsFromMappings = $rentalMappings->pluck('party_id')->unique()->toArray();

    //     $details = collect();
    //     $total = 0;
    //     $allDetailsForSummary = collect();

    //     if (!empty($partyIdsFromMappings)) {
            
    //         $postingsQuery = RentalPosting::with(['rentalParty'])
    //             ->where('status', 'approved')
    //             ->whereIn('head_id', $partyIdsFromMappings);
    //         if ($transactionType !== 'all') {
    //             $postingsQuery->where('entry_type', $transactionType);
    //         }

    //         // Apply the date filter to postings
    //         if (!empty($startDate) && !empty($endDate)) {
    //             $postingsQuery->whereBetween('posting_date', [$startDate, $endDate]);
    //         }

    //         $postings = $postingsQuery
    //             ->orderBy('posting_date', 'DESC')
    //             ->orderBy('id', 'DESC')
    //             ->get();
            
    //         $postingDetails = collect();
    //         foreach ($postings as $posting) {
    //             $postingDetails->push($this->formatPostingData($posting, $rentalMappings));
    //         }

    //         $syntheticDetails = collect();
    //         foreach ($rentalMappings as $mapping) {
    //             // Check if we should include this mapping based on filters
    //             $shouldInclude = true;
    //             if (!empty($headId) && $mapping->party_id != $headId) {
    //                 $shouldInclude = false;
    //             }
    //             if (!empty($houseId) && $mapping->house_id != $houseId) {
    //                 $shouldInclude = false;
    //             }

    //             if ($shouldInclude) {
    //                 // For each mapping, check if we need to create synthetic entries for different rent periods
    //                 $syntheticEntries = $this->createSyntheticEntriesForMapping($mapping, $postings);
    //                 $syntheticDetails = $syntheticDetails->merge($syntheticEntries);
    //             }
    //         }

    //         // This collection contains only the details relevant to the date filter
    //         $allDetailsForSummary = $postingDetails->merge($syntheticDetails);
    //         // Remove duplicates - if a posting already exists for a mapping, don't show synthetic entry
    //         $allDetailsForSummary = $this->removeDuplicateEntries($allDetailsForSummary);
    //         $total = $allDetailsForSummary->count();

    //         // The summary is calculated ONLY from the details which have been filtered by date
    //         $summary = $this->calculateLedgerSummaryFromDetails($allDetailsForSummary, $rentalMappings, $startDate, $endDate);
    //     }

    //     $details = $allDetailsForSummary->slice(($page - 1) * $pageSize, $pageSize)->values();

    //     return response()->json([
    //         'summary' => $summary,
    //         'details' => $details,
    //         'total' => $total,
    //     ]);
    // }




    private function createSyntheticEntriesForMapping($rentalMapping, $postings)
    {
        $syntheticEntries = collect();
        
        
        $allMappingsForHouse = RentalMapping::where('party_id', $rentalMapping->party_id)
            ->where('house_id', $rentalMapping->house_id)
            ->orderBy('rent_start_date', 'asc')
            ->get();

        foreach ($allMappingsForHouse as $mapping) {
            
            $hasPostingsForThisPeriod = $postings
                ->where('head_id', $mapping->party_id)
                ->where('house_id', $mapping->house_id)
                ->filter(function($posting) use ($mapping) {
                    $postingDate = \Carbon\Carbon::parse($posting->posting_date);
                    
                    
                    $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfDay();
                    $endDate = $mapping->rent_end_date ? \Carbon\Carbon::parse($mapping->rent_end_date)->endOfDay() : null;
                    
                   
                    return $postingDate->gte($startDate) && 
                           (!$endDate || $postingDate->lte($endDate));
                })
                ->isNotEmpty();

            
            if (!$hasPostingsForThisPeriod) {
                $syntheticEntries->push($this->createSyntheticDetailForPeriod($mapping));
            }
        }

        return $syntheticEntries;
    }

    // private function createSyntheticEntriesForMapping($rentalMapping, $postings, $startDate = null, $endDate = null)
    // {
    //     $syntheticEntries = collect();
        
    //     $allMappingsForHouse = RentalMapping::where('party_id', $rentalMapping->party_id)
    //         ->where('house_id', $rentalMapping->house_id)
    //         ->orderBy('rent_start_date', 'asc')
    //         ->get();

    //     foreach ($allMappingsForHouse as $mapping) {
            
    //         $hasPostingsForThisPeriod = $postings
    //             ->where('head_id', $mapping->party_id)
    //             ->where('house_id', $mapping->house_id)
    //             ->filter(function($posting) use ($mapping) {
    //                 $postingDate = \Carbon\Carbon::parse($posting->posting_date);
                    
    //                 $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfDay();
    //                 $endDate = $mapping->rent_end_date ? \Carbon\Carbon::parse($mapping->rent_end_date)->endOfDay() : null;
                    
    //                 return $postingDate->gte($startDate) && 
    //                     (!$endDate || $postingDate->lte($endDate));
    //             })
    //             ->isNotEmpty();

            
    //         if (!$hasPostingsForThisPeriod) {
    //             $syntheticEntries->push($this->createSyntheticDetailForPeriod($mapping, $startDate, $endDate));
    //         }
    //     }

    //     return $syntheticEntries;
    // }

 

    private function removeDuplicateEntries($details)
    {
        $uniqueEntries = collect();
        $processedKeys = [];

        foreach ($details as $entry) {
            // Check if is_synthetic key exists to avoid the error
            $isSynthetic = isset($entry['is_synthetic']) ? $entry['is_synthetic'] : false;
            
            // Create a unique key based on party, house, and posting date (for actual postings)
            // or rent period (for synthetic entries)
            if ($isSynthetic) {
                $rentPeriodStart = isset($entry['rent_period_start']) ? $entry['rent_period_start'] : '';
                $rentPeriodEnd = isset($entry['rent_period_end']) ? $entry['rent_period_end'] : '';
                $key = "{$entry['party_name']}_{$entry['house_id']}_{$rentPeriodStart}_{$rentPeriodEnd}";
            } else {
                $postingDate = isset($entry['posting_date']) ? $entry['posting_date'] : '';
                $key = "{$entry['party_name']}_{$entry['house_id']}_{$postingDate}";
            }

            if (!in_array($key, $processedKeys)) {
                $uniqueEntries->push($entry);
                $processedKeys[] = $key;
            }
        }

        return $uniqueEntries;
    }

   

    // private function calculateLedgerSummaryFromDetails($allDetails, $rentalMappings)
    // {
    //     $totalMonthlyRent = 0;
    //     $totalRentReceived = 0;
    //     $totalSecurityMoney = 0;
    //     $totalAutoAdjustment = 0;
    //     $totalSecurityRefund = 0;
    //     $totalReceivable = 0;
    //     $totalRemainingSecurity = 0;
        
        
    //     $finalReceivablesByMapping = [];
        
        
    //     $totalMonthlyRent = $rentalMappings->where('status', 'active')->sum('monthly_rent');
        
    //     $processedMappings = [];
        
    //     foreach ($allDetails as $detail) {
            
    //         $mappingKey = ($detail['party_name'] ?? '') . '_' . ($detail['house_id'] ?? '');
            
            
            
    //         if (!in_array($mappingKey, $processedMappings)) {
    //             $processedMappings[] = $mappingKey;
                
                
                
               
    //             if (isset($detail['security_money'])) {
    //                 $totalSecurityMoney += (float)str_replace(',', '', $detail['security_money']); // [cite: 42]
    //             }
                
                
    //             if (isset($detail['remaining_security_money'])) {
    //                 $totalRemainingSecurity += (float)str_replace(',', ',', $detail['remaining_security_money']); // [cite: 44]
    //             }
    //         }
            
            
            
    //         if (isset($detail['entry_type'])) {
                
    //             if ($detail['entry_type'] === 'rent_received' && isset($detail['amount_bdt'])) {
    //                 $totalRentReceived += (float)str_replace(',', '', $detail['amount_bdt']); // [cite: 45]
    //             }
                
                
    //             if ($detail['entry_type'] === 'auto_adjustment' && isset($detail['auto_adjust_amount'])) {
    //                 $totalAutoAdjustment += (float)$detail['auto_adjust_amount']; // [cite: 47]
    //             }
                
                
    //             if ($detail['entry_type'] === 'security_money_refund' && isset($detail['refund_amount'])) {
    //                 $totalSecurityRefund += (float)$detail['refund_amount']; // [cite: 48]
    //             }
    //         }
            
            
            
    //         if (isset($detail['total_receivable']) && $detail['total_receivable'] !== '--') {
                 
    //             $finalReceivablesByMapping[$mappingKey] = (float)str_replace(',', '', $detail['total_receivable']);
    //         }
    //     }
        
        
    //     $totalReceivable = array_sum($finalReceivablesByMapping);
        
        
    //     $totalDueMonth = 0; // [cite: 50]
    //     $partialDueAmount = 0; // [cite: 50]
        
        
    //     if ($totalReceivable > 0 && $totalMonthlyRent > 0) {
    //         $fullDueMonths = (int)($totalReceivable / $totalMonthlyRent); // [cite: 51]
    //         $totalDueMonth = $fullDueMonths; // [cite: 51]
    //         $partialDueAmount = fmod($totalReceivable, $totalMonthlyRent);
            
    //         if ($partialDueAmount > 0.01) {
    //             $totalDueMonth += 1; // [cite: 52]
    //         } else {
    //             $partialDueAmount = 0; // [cite: 53]
    //         }
    //     }

    //     return [
    //         'total_monthly_rent' => round($totalMonthlyRent, 2), // [cite: 54]
    //         'total_rent_received' => number_format($totalRentReceived, 2, '.', ''), 
    //         'total_security_money' => round($totalSecurityMoney, 2), // [cite: 54]
    //         'total_auto_adjustment' => round($totalAutoAdjustment, 2), // [cite: 54]
    //         'total_remaining_security_money' => round($totalRemainingSecurity, 2), // [cite: 54]
    //         'total_security_refund' => round($totalSecurityRefund, 2), // [cite: 54]
    //         'total_receivable' => round($totalReceivable, 2), // [cite: 54]
    //         'total_due_amount' => round($totalReceivable, 2), // [cite: 54]
    //         'total_due_month' => (int)$totalDueMonth, // [cite: 54]
    //         'partial_due_amount' => round($partialDueAmount, 2), // [cite: 54]
    //         'already_adjusted' => round($totalAutoAdjustment, 2), // [cite: 54]
    //     ];
    // }


    // private function calculateLedgerSummaryFromDetails($allDetails, $rentalMappings, $startDate, $endDate)
    // {
    //     $totalMonthlyRent = 0;
    //     $totalRentReceived = 0;
    //     $totalSecurityMoney = 0;
    //     $totalAutoAdjustment = 0;
    //     $totalSecurityRefund = 0;
    //     $totalExpectedRent = 0;
    //     $totalReceivable = 0;
    //     $totalRemainingSecurity = 0;
    //     $finalReceivablesByMapping = [];
        
    //     // Get current month's start and end dates
    //     $currentDate = \Carbon\Carbon::now();
    //     $currentMonthStart = $currentDate->startOfMonth()->toDateString();
    //     $currentMonthEnd = $currentDate->endOfMonth()->toDateString();
        
    //     // ========== TOTAL MONTHLY RENT - CURRENT MONTH ONLY ==========
    //     // Sum only active mappings (represents what SHOULD be paid this month)
    //     $totalMonthlyRent = $rentalMappings->where('status', 'active')->sum('monthly_rent');
        
    //     // ========== TOTAL RENT RECEIVED - CURRENT MONTH ONLY ==========
    //     $partyIds = $rentalMappings->pluck('party_id')->unique()->toArray();
    //     if (!empty($partyIds)) {
    //         $rentReceivedQuery = RentalPosting::whereIn('head_id', $partyIds)
    //             ->where('entry_type', 'rent_received')
    //             ->where('status', 'approved')
    //             // Always filter by CURRENT MONTH, regardless of selected date range
    //             ->whereBetween('posting_date', [$currentMonthStart, $currentMonthEnd]);
            
    //         $totalRentReceived = $rentReceivedQuery->sum('amount_bdt');
    //     }
        
    //     // ========== OTHER SUMMARIES - FULL DATE RANGE ==========
    //     // Get ALL mappings (not just active) to match the original logic
    //     $allMappingsQuery = RentalMapping::where('status', '!=', 'deleted');
        
    //     if (!empty($partyIds)) {
    //         $allMappingsQuery->whereIn('party_id', $partyIds);
    //     }
        
    //     $allMappings = $allMappingsQuery->get();
        
    //     // Group all mappings by party and house
    //     $mappingsByPartyHouse = $allMappings->groupBy(function($item) {
    //         return $item->party_id . '_' . $item->house_id;
    //     });
        
    //     $processedMappings = [];
        
    //     // Calculate expected rent and other details from ALL mappings based on date range
    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');
            
    //         foreach ($sortedMappings as $mapping) {
    //             $mappingKey = $mapping->party_id . '_' . $mapping->house_id;
                
    //             if (!in_array($mappingKey, $processedMappings)) {
    //                 $processedMappings[] = $mappingKey;
                    
    //                 $totalSecurityMoney += (float)$mapping->security_money;
    //                 $totalRemainingSecurity += (float)$mapping->remaining_security_money;
    //             }
                
    //             // Calculate expected rent based on date range (for each mapping, not just once)
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDateObj = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
    //                 $endDateObj = \Carbon\Carbon::parse($endDate)->startOfMonth();
                    
    //                 // If mapping has an end date, use the earlier of the two
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDateObj)) {
    //                         $endDateObj = $mappingEndDate;
    //                     }
    //                 }

    //                 // Ensure start date is not after end date
    //                 if ($startDateObj->lte($endDateObj)) {
    //                     $months = $startDateObj->diffInMonths($endDateObj) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }
    //     }
        
    //     // Calculate received amounts from postings (filtered by date range)
    //     foreach ($allDetails as $detail) {
    //         if (isset($detail['entry_type'])) {
    //             if ($detail['entry_type'] === 'auto_adjustment' && isset($detail['auto_adjust_amount'])) {
    //                 $totalAutoAdjustment += (float)$detail['auto_adjust_amount'];
    //             }
                
    //             if ($detail['entry_type'] === 'security_money_refund' && isset($detail['refund_amount'])) {
    //                 $totalSecurityRefund += (float)$detail['refund_amount'];
    //             }
    //         }
    //     }
        
    //     // Calculate receivable amount
    //     $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
    //     // Calculate due amount
    //     $totalDueAmount = $totalReceivable;
        
    //     // Calculate due months and partial amount
    //     $totalDueMonth = 0;
    //     $partialDueAmount = 0;
        
    //     if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //         $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //         $totalDueMonth = $fullMonths;
    //         $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
    //         if ($partialDueAmount > 0.01) {
    //             $totalDueMonth += 1;
    //         } else {
    //             $partialDueAmount = 0;
    //         }
    //     }

    //     return [
    //         'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //         'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
    //         'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //         'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //         'total_remaining_security_money' => number_format($totalRemainingSecurity, 2, '.', ''),
    //         'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
    //         'total_expected_rent' => number_format($totalExpectedRent, 2, '.', ''),
    //         'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //         'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //         'total_due_month' => (int)$totalDueMonth,
    //         'partial_due_amount' => round($partialDueAmount, 2),
    //         'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
    //     ];
    // }

    // due in current month

    // private function calculateLedgerSummaryFromDetails($allDetails, $rentalMappings, $startDate, $endDate)
    // {
    //     $totalMonthlyRent = 0;
    //     $totalRentReceived = 0;
    //     $totalSecurityMoney = 0;
    //     $totalAutoAdjustment = 0;
    //     $totalSecurityRefund = 0;
    //     $totalExpectedRent = 0;
    //     $totalReceivable = 0;
    //     $totalRemainingSecurity = 0;
    //     $finalReceivablesByMapping = [];
        
    //     // ========== TOTAL MONTHLY RENT - CURRENT ACTIVE MAPPINGS ONLY ==========
    //     // Sum only active mappings (represents what SHOULD be paid currently)
    //     $totalMonthlyRent = $rentalMappings->where('status', 'active')->sum('monthly_rent');
        
    //     // ========== TOTAL RENT RECEIVED - SELECTED DATE RANGE ==========
    //     // Use the same date range as expected rent for consistency
    //     $partyIds = $rentalMappings->pluck('party_id')->unique()->toArray();
    //     if (!empty($partyIds)) {
    //         $rentReceivedQuery = RentalPosting::whereIn('head_id', $partyIds)
    //             ->where('entry_type', 'rent_received')
    //             ->where('status', 'approved');
            
    //         // Apply the SAME date range filter
    //         if (!empty($startDate) && !empty($endDate)) {
    //             $rentReceivedQuery->whereBetween('posting_date', [$startDate, $endDate]);
    //         }
            
    //         $totalRentReceived = $rentReceivedQuery->sum('amount_bdt');
    //     }
        
    //     // ========== OTHER SUMMARIES - FULL DATE RANGE ==========
    //     // Get ALL mappings (not just active) for security money and other details
    //     $allMappingsQuery = RentalMapping::where('status', '!=', 'deleted');
        
    //     if (!empty($partyIds)) {
    //         $allMappingsQuery->whereIn('party_id', $partyIds);
    //     }
        
    //     $allMappings = $allMappingsQuery->get();
        
    //     // Group all mappings by party and house
    //     $mappingsByPartyHouse = $allMappings->groupBy(function($item) {
    //         return $item->party_id . '_' . $item->house_id;
    //     });
        
    //     $processedMappings = [];
        
    //     // Calculate expected rent and other details from ALL mappings based on date range
    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');
            
    //         foreach ($sortedMappings as $mapping) {
    //             $mappingKey = $mapping->party_id . '_' . $mapping->house_id;
                
    //             if (!in_array($mappingKey, $processedMappings)) {
    //                 $processedMappings[] = $mappingKey;
                    
    //                 $totalSecurityMoney += (float)$mapping->security_money;
    //                 $totalRemainingSecurity += (float)$mapping->remaining_security_money;
    //             }
                
    //             // Calculate expected rent based on date range (for each mapping, not just once)
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDateObj = \Carbon\Carbon::parse($startDate)->startOfMonth();
    //                 $endDateObj = \Carbon\Carbon::parse($endDate)->startOfMonth();
                    
    //                 $mappingStartDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
    //                 // Use the later of: selected start date OR mapping start date
    //                 if ($mappingStartDate->gt($startDateObj)) {
    //                     $startDateObj = $mappingStartDate;
    //                 }
                    
    //                 // If mapping has an end date, use the earlier of the two
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDateObj)) {
    //                         $endDateObj = $mappingEndDate;
    //                     }
    //                 }

    //                 // Ensure start date is not after end date
    //                 if ($startDateObj->lte($endDateObj)) {
    //                     $months = $startDateObj->diffInMonths($endDateObj) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }
    //     }
        
    //     // Calculate received amounts from postings (filtered by date range)
    //     foreach ($allDetails as $detail) {
    //         if (isset($detail['entry_type'])) {
    //             if ($detail['entry_type'] === 'auto_adjustment' && isset($detail['auto_adjust_amount'])) {
    //                 $totalAutoAdjustment += (float)$detail['auto_adjust_amount'];
    //             }
                
    //             if ($detail['entry_type'] === 'security_money_refund' && isset($detail['refund_amount'])) {
    //                 $totalSecurityRefund += (float)$detail['refund_amount'];
    //             }
    //         }
    //     }
        
    //     // Calculate receivable amount
    //     $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
    //     // Calculate due amount
    //     $totalDueAmount = $totalReceivable;
        
    //     // Calculate due months and partial amount
    //     $totalDueMonth = 0;
    //     $partialDueAmount = 0;
        
    //     if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //         $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //         $totalDueMonth = $fullMonths;
    //         $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
    //         if ($partialDueAmount > 0.01) {
    //             $totalDueMonth += 1;
    //         } else {
    //             $partialDueAmount = 0;
    //         }
    //     }

    //     return [
    //         'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //         'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
    //         'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //         'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //         'total_remaining_security_money' => number_format($totalRemainingSecurity, 2, '.', ''),
    //         'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
    //         'total_expected_rent' => number_format($totalExpectedRent, 2, '.', ''),
    //         'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //         'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //         'total_due_month' => (int)$totalDueMonth,
    //         'partial_due_amount' => round($partialDueAmount, 2),
    //         'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
    //     ];
    // }


    private function calculateLedgerSummaryFromDetails($allDetails, $rentalMappings, $startDate, $endDate)
    {
        $totalMonthlyRent = 0;
        $totalRentReceived = 0;
        $totalSecurityMoney = 0;
        $totalAutoAdjustment = 0;
        $totalSecurityRefund = 0;
        $totalExpectedRent = 0;
        $totalReceivable = 0;
        $totalRemainingSecurity = 0;
        
        
        $totalMonthlyRent = $rentalMappings->where('status', 'active')->sum('monthly_rent');
        
        
        $partyIds = $rentalMappings->pluck('party_id')->unique()->toArray();
        $allMappingsQuery = RentalMapping::where('status', '!=', 'deleted');
        
        if (!empty($partyIds)) {
            $allMappingsQuery->whereIn('party_id', $partyIds);
        }
        
        $allMappings = $allMappingsQuery->get();
        
        
        $mappingsByPartyHouse = $allMappings->groupBy(function($item) {
            return $item->party_id . '_' . $item->house_id;
        });
        
        $processedMappings = [];
        $selectedStartDate = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $selectedEndDate = \Carbon\Carbon::parse($endDate)->startOfMonth();
        
        
        foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
            $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');
            
            foreach ($sortedMappings as $mapping) {
                $mappingKey = $mapping->party_id . '_' . $mapping->house_id;
                
                
                if (!in_array($mappingKey, $processedMappings)) {
                    $processedMappings[] = $mappingKey;
                    $totalSecurityMoney += (float)$mapping->security_money;
                    $totalRemainingSecurity += (float)$mapping->remaining_security_money;
                }
                
                
                if ($mapping->rent_start_date && $mapping->monthly_rent) {
                    $mappingStartDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
                    
                    $periodStart = $mappingStartDate->gt($selectedStartDate) ? $mappingStartDate : $selectedStartDate;
                    $periodEnd = $selectedEndDate;
                    
                    
                    if ($mapping->rent_end_date) {
                        $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
                        $periodEnd = $mappingEndDate->lt($selectedEndDate) ? $mappingEndDate : $selectedEndDate;
                    }
                    
                    
                    if ($periodStart->lte($periodEnd)) {
                        $months = $periodStart->diffInMonths($periodEnd) + 1;
                        $totalExpectedRent += $months * (float)$mapping->monthly_rent;
                    }
                }
            }
        }
        
        
        if (!empty($partyIds)) {
            $totalRentReceived = RentalPosting::whereIn('head_id', $partyIds)
                ->where('entry_type', 'rent_received')
                ->where('status', 'approved')
                ->whereBetween('posting_date', [$startDate, $endDate])
                ->sum('amount_bdt');
        }
        
        
        foreach ($allDetails as $detail) {
            if (isset($detail['entry_type'])) {
                if ($detail['entry_type'] === 'auto_adjustment' && isset($detail['auto_adjust_amount'])) {
                    $totalAutoAdjustment += (float)$detail['auto_adjust_amount'];
                }
                
                if ($detail['entry_type'] === 'security_money_refund' && isset($detail['refund_amount'])) {
                    $totalSecurityRefund += (float)$detail['refund_amount'];
                }
            }
        }
        
        
        $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
        
        $totalDueAmount = $totalReceivable;
        
        
        $totalDueMonth = 0;
        $partialDueAmount = 0;
        
        if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
            $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
            $totalDueMonth = $fullMonths;
            $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
            if ($partialDueAmount > 0.01) {
                $totalDueMonth += 1;
            } else {
                $partialDueAmount = 0;
            }
        }

        return [
            'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
            'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
            'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
            'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
            'total_remaining_security_money' => number_format($totalRemainingSecurity, 2, '.', ''),
            'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
            'total_expected_rent' => number_format($totalExpectedRent, 2, '.', ''),
            'total_receivable' => number_format($totalReceivable, 2, '.', ''),
            'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
            'total_due_month' => (int)$totalDueMonth,
            'partial_due_amount' => round($partialDueAmount, 2),
            'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
        ];
    }



    private function formatPostingData($posting, $rentalMappings)
    {
        
        $partyHouseMappings = RentalMapping::where('party_id', $posting->head_id)
            ->where('house_id', $posting->house_id)
            ->orderBy('rent_start_date', 'asc')
            ->get();

        
        $postingDate = \Carbon\Carbon::parse($posting->posting_date);
        $postingYearMonth = $postingDate->format('Y-m'); 
        
        $currentMapping = null;
        
        foreach ($partyHouseMappings as $mapping) {
            $mappingStartYearMonth = \Carbon\Carbon::parse($mapping->rent_start_date)->format('Y-m');
            $mappingEndYearMonth = $mapping->rent_end_date 
                ? \Carbon\Carbon::parse($mapping->rent_end_date)->format('Y-m') 
                : null;
            
        
            if ($postingYearMonth >= $mappingStartYearMonth) {
                if (!$mappingEndYearMonth || $postingYearMonth <= $mappingEndYearMonth) {
                    $currentMapping = $mapping;
                    break;
                }
            }
        }

        
        if (!$currentMapping) {
            $currentMapping = $partyHouseMappings->first();
        }

        
        $monthlyRent = $currentMapping ? $currentMapping->monthly_rent : 0;
        $houseName = $currentMapping && $currentMapping->rentalHouse ? $currentMapping->rentalHouse->house_name : '--';
        $rentStartDate = $currentMapping ? $currentMapping->rent_start_date : null;
        $rentEndDate = $currentMapping ? $currentMapping->rent_end_date : null;
        $securityMoney = $currentMapping ? $currentMapping->security_money : 0;
        $remainingSecurity = $currentMapping ? $currentMapping->remaining_security_money : 0;
        $mappingPeriodStart = $rentStartDate;
        $mappingPeriodEnd = $rentEndDate;

    
        $totalExpectedRentForPeriod = 0;
        $currentDate = now();

        if ($currentMapping && $currentMapping->rent_start_date && $currentMapping->monthly_rent) {
            $mStart = \Carbon\Carbon::parse($currentMapping->rent_start_date)->startOfMonth();
            $mEnd = $currentDate->copy()->startOfMonth();
            
            if ($currentMapping->rent_end_date) {
                $mappingEnd = \Carbon\Carbon::parse($currentMapping->rent_end_date)->startOfMonth();
                if ($mappingEnd->lt($mEnd)) {
                    $mEnd = $mappingEnd;
                }
            }

            if ($mStart->lte($mEnd)) {
                $months = $mStart->diffInMonths($mEnd) + 1;
                $totalExpectedRentForPeriod = $months * $currentMapping->monthly_rent;
            }
        }

        
        $totalRentReceivedForPeriod = 0;
        
        if ($currentMapping) {
            $query = RentalPosting::where('head_id', $posting->head_id)
                ->where('house_id', $posting->house_id)
                ->where('entry_type', 'rent_received')
                ->where('status', 'approved');
            
            
            $startYearMonth = \Carbon\Carbon::parse($currentMapping->rent_start_date)->format('Y-m');
            $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') >= ?", [$startYearMonth]);
            
            if ($currentMapping->rent_end_date) {
                $endYearMonth = \Carbon\Carbon::parse($currentMapping->rent_end_date)->format('Y-m');
                $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') <= ?", [$endYearMonth]);
            }
            
            $totalRentReceivedForPeriod = $query->sum('amount_bdt');
        }

        
        $totalReceivableForPeriod = max(0, round($totalExpectedRentForPeriod - $totalRentReceivedForPeriod, 2));

        
        $totalExpectedRentAtPosting = 0;
        $postingDateForCalculation = $postingDate->copy()->startOfMonth();

        if ($currentMapping && $currentMapping->rent_start_date && $currentMapping->monthly_rent) {
            $mStart = \Carbon\Carbon::parse($currentMapping->rent_start_date)->startOfMonth();
            $mEnd = $postingDateForCalculation;
            
            if ($currentMapping->rent_end_date) {
                $mappingEnd = \Carbon\Carbon::parse($currentMapping->rent_end_date)->startOfMonth();
                if ($mappingEnd->lt($mEnd)) {
                    $mEnd = $mappingEnd;
                }
            }
            
            if ($mStart->lte($mEnd)) {
                $months = $mStart->diffInMonths($mEnd) + 1;
                $totalExpectedRentAtPosting = $months * $currentMapping->monthly_rent;
            }
        }

        
        $totalRentReceivedAtPosting = 0;
        
        if ($currentMapping) {
            $query = RentalPosting::where('head_id', $posting->head_id)
                ->where('house_id', $posting->house_id)
                ->where('entry_type', 'rent_received')
                ->where('status', 'approved')
                ->whereDate('posting_date', '<=', $postingDate);
            
            
            $startYearMonth = \Carbon\Carbon::parse($currentMapping->rent_start_date)->format('Y-m');
            $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') >= ?", [$startYearMonth]);
            
            if ($currentMapping->rent_end_date) {
                $endYearMonth = \Carbon\Carbon::parse($currentMapping->rent_end_date)->format('Y-m');
                $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') <= ?", [$endYearMonth]);
            }
            
            $totalRentReceivedAtPosting = $query->sum('amount_bdt');
        }

        $totalReceivableAtPosting = max(0, round($totalExpectedRentAtPosting - $totalRentReceivedAtPosting, 2));

        
        $totalDueMonth = 0;
        $partialDueAmount = 0;

        if ($totalReceivableForPeriod > 0 && $monthlyRent > 0) {
            $fullDueMonths = (int)($totalReceivableForPeriod / $monthlyRent);
            $totalDueMonth = $fullDueMonths;
            $partialDueAmount = fmod($totalReceivableForPeriod, $monthlyRent);
            
            if ($partialDueAmount > 0.01) {
                $totalDueMonth += 1;
            } else {
                $partialDueAmount = 0;
            }
        }

    
        $dueAmountToDisplay = $totalReceivableForPeriod;
        $dueMonthToDisplay = $totalDueMonth;
        $partialDueToDisplay = $partialDueAmount;

        if ($totalReceivableAtPosting == 0 && $totalReceivableForPeriod > 0) {
            
            $dueAmountToDisplay = 0;
            $dueMonthToDisplay = 0;
            $partialDueToDisplay = 0;
        }

        
        $showDue = $posting->entry_type === 'rent_received';
        
        
        $finalPartialDueAmount = number_format((float)$partialDueToDisplay, 2, '.', '');

    
        $alreadyAdjusted = 0;
        if ($currentMapping) {
            $query = RentalPosting::where('head_id', $posting->head_id)
                ->where('house_id', $posting->house_id)
                ->where('entry_type', 'auto_adjustment')
                ->where('status', 'approved');
            
            $startYearMonth = \Carbon\Carbon::parse($currentMapping->rent_start_date)->format('Y-m');
            $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') >= ?", [$startYearMonth]);
            
            if ($currentMapping->rent_end_date) {
                $endYearMonth = \Carbon\Carbon::parse($currentMapping->rent_end_date)->format('Y-m');
                $query->whereRaw("DATE_FORMAT(posting_date, '%Y-%m') <= ?", [$endYearMonth]);
            }
            
            $alreadyAdjusted = $query->sum('amount_bdt');
        }

        return [
            'id' => $posting->id,
            'entry_type' => $posting->entry_type,
            'party_name' => $posting->rentalParty->party_name ?? '--',
            'house_name' => $houseName,
            'rent' => in_array($posting->entry_type, ['security_money_amount', 'security_money_refund', 'auto_adjustment'])
                ? '--'
                : number_format((float)$monthlyRent, 2, '.', ''), 
            'security_money' => number_format((float)$securityMoney, 2, '.', ''),
            'remaining_security_money' => number_format((float)$remainingSecurity, 2, '.', ''),
            'auto_adjust_amount' => $posting->entry_type === 'auto_adjustment' ? $posting->amount_bdt : 0,
            'already_adjusted' => $alreadyAdjusted,
            'total_received' => number_format((float)$totalRentReceivedForPeriod, 2, '.', ''),
            'refund_amount' => $posting->entry_type === 'security_money_refund' ? $posting->amount_bdt : 0,
            'total_receivable' => $showDue ? number_format((float)$dueAmountToDisplay, 2, '.', '') : '--',
            'total_due_amount' => $showDue ? number_format((float)$dueAmountToDisplay, 2, '.', '') : '--',
            'total_due_month' => $showDue ? (int)$dueMonthToDisplay : '--', 
            'partial_due_amount' => $showDue ? $finalPartialDueAmount : '--',
            'posting_date' => $posting->posting_date,
            'amount_bdt' => number_format((float)$posting->amount_bdt, 2, '.', ''),
            'note' => $posting->note,
            'house_id' => $posting->house_id,
            'rent_start_date' => $rentStartDate,
            'rent_end_date' => $rentEndDate,
            'is_synthetic' => false,
            'mapping_period_start' => $mappingPeriodStart,
            'mapping_period_end' => $mappingPeriodEnd,
        ];
    }


    private function createSyntheticDetailForPeriod($rentalMapping)
    {
        if (is_array($rentalMapping)) {
            $rentalMapping = (object) $rentalMapping;
        }

        $currentDate = now();

        // Initialize variables
        $expectedRentForHouse = 0;
        $totalDueMonth = 0;
        $partialDueAmount = 0;
        $monthlyRent = $rentalMapping->monthly_rent ?? 0;

        if (isset($rentalMapping->rent_start_date) && $rentalMapping->rent_start_date && $monthlyRent) {
            
            
            $rentStartDate = \Carbon\Carbon::parse($rentalMapping->rent_start_date)->startOfMonth();
            
            
            $effectiveEndDate = $currentDate->copy()->startOfMonth(); 
            
            if ($rentalMapping->rent_end_date) {
                $rentEndDate = \Carbon\Carbon::parse($rentalMapping->rent_end_date)->startOfMonth();
                
                
                if ($rentEndDate->lt($effectiveEndDate)) {
                    $effectiveEndDate = $rentEndDate;
                }
            }

            // Only calculate if rent start date is on or before effective end date
            if ($rentStartDate->lte($effectiveEndDate)) {
                
                // Calculate total months using startOfMonth comparison to ensure 1 month is calculated correctly
                // e.g., Nov 1 to Nov 1 (diff 0) + 1 = 1 Month.
                $diffInMonths = $rentStartDate->diffInMonths($effectiveEndDate);
                $totalMonths = $diffInMonths + 1;
                $totalMonths = max(0, $totalMonths);

                if ($totalMonths > 0) {
                    $expectedRentForHouse = $totalMonths * $monthlyRent;
                }

                // Define exact range for checking received rent (Start of StartMonth to End of EndMonth)
                $queryStartDate = \Carbon\Carbon::parse($rentalMapping->rent_start_date)->startOfMonth();
                $queryEndDate = $effectiveEndDate->copy()->endOfMonth();

                // Calculate rent received strictly for THIS SPECIFIC PERIOD
                $partyRentReceived = RentalPosting::where('head_id', $rentalMapping->party_id)
                    ->where('house_id', $rentalMapping->house_id)
                    ->where('entry_type', 'rent_received')
                    ->where('status', 'approved')
                    ->whereDate('posting_date', '>=', $queryStartDate)
                    ->whereDate('posting_date', '<=', $queryEndDate)
                    ->sum('amount_bdt');

                // Calculate actual receivable (Expected - Received)
                // Use round() to avoid floating point precision issues like 999999.9999
                $actualReceivable = max(0, round($expectedRentForHouse - $partyRentReceived, 2));
                
                // If there's any receivable, calculate months due
                if ($actualReceivable > 0 && $monthlyRent > 0) {
                    // Calculate base due months (integer division)
                    // e.g. 500000 / 500000 = 1
                    $totalDueMonth = (int)($actualReceivable / $monthlyRent);
                    
                    // Calculate remaining partial amount
                    $partialDueAmount = fmod($actualReceivable, $monthlyRent);
                    
                    // If there is any partial amount left (e.g., 0.5 month due), add 1 to month count
                    // Use a small epsilon for float comparison to avoid false positives
                    if ($partialDueAmount > 0.01) {
                        $totalDueMonth += 1;
                    }
                } else {
                    $totalDueMonth = 0;
                    $partialDueAmount = 0;
                }
                
                // Final assignment of receivable
                $expectedRentForHouse = $actualReceivable;
            }
        }

        // Get party and house names
        $partyName = isset($rentalMapping->rentalParty) ? ($rentalMapping->rentalParty->party_name ?? '--') : ($rentalMapping->party_name ?? '--');
        $houseName = isset($rentalMapping->rentalHouse) ? ($rentalMapping->rentalHouse->house_name ?? '--') : ($rentalMapping->house_name ?? '--');

        // Create appropriate note
        $note = 'No transactions for this rent period';
        if ($rentalMapping->rent_start_date && $rentalMapping->rent_end_date) {
            $start = \Carbon\Carbon::parse($rentalMapping->rent_start_date)->format('M Y');
            $end = \Carbon\Carbon::parse($rentalMapping->rent_end_date)->format('M Y');
            $note = "Rent period: {$start} to {$end} - No transactions";
        } elseif ($rentalMapping->rent_start_date) {
            $start = \Carbon\Carbon::parse($rentalMapping->rent_start_date)->format('M Y');
            $note = "Rent period: {$start} onwards - No transactions";
        }

        return [
            'id' => null,
            'entry_type' => 'No Transaction',
            'party_name' => $partyName,
            'house_name' => $houseName,
            'rent' => number_format((float)$monthlyRent, 2, '.', ''),
            'security_money' => number_format((float)($rentalMapping->security_money ?? 0), 2, '.', ''),
            'remaining_security_money' => $rentalMapping->remaining_security_money ?? 0,
            'auto_adjust_amount' => 0,
            'already_adjusted' => 0,
            'total_received' => 0,
            'refund_amount' => 0,
            'total_receivable' => number_format((float)$expectedRentForHouse, 2, '.', ''),
            'total_due_amount' => number_format((float)$expectedRentForHouse, 2, '.', ''),
            'total_due_month' => (int)$totalDueMonth,
            'partial_due_amount' => number_format((float)$partialDueAmount, 2, '.', ''),
            'posting_date' => '--',
            'amount_bdt' => 0,
            'note' => $note,
            'is_synthetic' => true,
            'house_id' => $rentalMapping->house_id ?? null,
            'rent_start_date' => $rentalMapping->rent_start_date ?? null,
            'rent_end_date' => $rentalMapping->rent_end_date ?? null,
            'rent_period_start' => $rentalMapping->rent_start_date ?? null,
            'rent_period_end' => $rentalMapping->rent_end_date ?? null,
        ];
    }



    private function getApplicableMonthlyRent($posting, $rentalMappings)
    {
        $postingDate = \Carbon\Carbon::parse($posting->posting_date);
        
        // Find all mappings for this party and house
        $mappingsForThisHouse = $rentalMappings->where('party_id', $posting->head_id)
                                            ->where('house_id', $posting->house_id)
                                            ->sortBy('rent_start_date');

        // Find the mapping that was active during the posting date
        $applicableMapping = null;
        
        foreach ($mappingsForThisHouse as $mapping) {
            $startDate = \Carbon\Carbon::parse($mapping->rent_start_date);
            $endDate = $mapping->rent_end_date ? \Carbon\Carbon::parse($mapping->rent_end_date) : null;
            
            // Check if posting date falls within this mapping's period
            if ($postingDate->gte($startDate)) {
                if (!$endDate || $postingDate->lte($endDate)) {
                    $applicableMapping = $mapping;
                    break;
                }
            }
        }

        // If no specific mapping found for the posting date, use the first one
        if (!$applicableMapping) {
            $applicableMapping = $mappingsForThisHouse->first();
        }

        return $applicableMapping ? $applicableMapping->monthly_rent : 0;
    }


    private function createSyntheticDetail($rentalMapping)
    {
        if (is_array($rentalMapping)) {
            $rentalMapping = (object) $rentalMapping;
        }

        $currentDate = now();

        // Calculate expected rent considering start and end dates
        $expectedRentForHouse = 0;
        $hasRentStartDate = false;
        $totalDueMonth = 0;
        $partialDueAmount = 0;

        if (
            isset($rentalMapping->rent_start_date) && $rentalMapping->rent_start_date &&
            isset($rentalMapping->monthly_rent) && $rentalMapping->monthly_rent
        ) {
            $hasRentStartDate = true;
            $rentStartDate = \Carbon\Carbon::parse($rentalMapping->rent_start_date);
            
            // Calculate effective end date
            $effectiveEndDate = $currentDate;
            if ($rentalMapping->rent_end_date) {
                $rentEndDate = \Carbon\Carbon::parse($rentalMapping->rent_end_date);
                if ($rentEndDate->lt($currentDate)) {
                    $effectiveEndDate = $rentEndDate;
                }
            }

            // Only calculate if rent start date is on or before effective end date
            if ($rentStartDate->lte($effectiveEndDate)) {
                // Calculate total months between start and end (inclusive)
                $totalMonths = $rentStartDate->diffInMonths($effectiveEndDate) + 1;
                $totalMonths = max(0, $totalMonths);

                if ($totalMonths > 0) {
                    $expectedRentForHouse = $totalMonths * $rentalMapping->monthly_rent;
                }

                // Calculate rent received for this specific mapping
                $partyRentReceived = RentalPosting::where('head_id', $rentalMapping->party_id)
                    ->where('house_id', $rentalMapping->house_id)
                    ->where('entry_type', 'rent_received')
                    ->where('status', 'approved')
                    ->sum('amount_bdt');

                // Calculate paid months
                $paidMonthsCount = RentalPosting::where('head_id', $rentalMapping->party_id)
                    ->where('house_id', $rentalMapping->house_id)
                    ->where('entry_type', 'rent_received')
                    ->where('status', 'approved')
                    ->selectRaw('COUNT(DISTINCT DATE_FORMAT(posting_date, "%Y-%m")) as paid_months')
                    ->value('paid_months') ?? 0;

                // Calculate due months
                $totalDueMonth = max(0, $totalMonths - $paidMonthsCount);

                // Recalculate expected rent based on actual receivable
                $actualReceivable = max(0, $expectedRentForHouse - $partyRentReceived);
                $partialDueAmount = $rentalMapping->monthly_rent > 0 ? fmod($actualReceivable, $rentalMapping->monthly_rent) : 0;
            }
        }

        // Get party and house names
        $partyName = '--';
        $houseName = '--';

        if (isset($rentalMapping->rentalParty) && is_object($rentalMapping->rentalParty)) {
            $partyName = $rentalMapping->rentalParty->party_name ?? '--';
        } elseif (isset($rentalMapping->party_name)) {
            $partyName = $rentalMapping->party_name;
        }

        if (isset($rentalMapping->rentalHouse) && is_object($rentalMapping->rentalHouse)) {
            $houseName = $rentalMapping->rentalHouse->house_name ?? '--';
        } elseif (isset($rentalMapping->house_name)) {
            $houseName = $rentalMapping->house_name;
        }

        return [
            'id' => null,
            'entry_type' => 'No Transaction',
            'party_name' => $partyName,
            'house_name' => $houseName,
            'rent' => $rentalMapping->monthly_rent ?? 0,
            'security_money' => $rentalMapping->security_money ?? 0,
            'remaining_security_money' => $rentalMapping->remaining_security_money ?? 0,
            'auto_adjust_amount' => 0,
            'already_adjusted' => 0,
            'total_received' => 0,
            'refund_amount' => 0,
            'total_receivable' => $expectedRentForHouse,
            'total_due_amount' => $expectedRentForHouse,
            'total_due_month' => $totalDueMonth,
            'partial_due_amount' => $partialDueAmount,
            'posting_date' => '--',
            'amount_bdt' => 0,
            'note' => 'No transactions yet',
            'is_synthetic' => true,
            'house_id' => $rentalMapping->house_id ?? null,
        ];
    }




    // public function getRentalLedgerSummary(Request $request)
    // {
    //     $filters = $request->query();

    //     $currentDate = now();
    //     $currentYear = $currentDate->year;
    //     $currentMonth = $currentDate->month;

    //     // Create a subquery that aggregates all rental house data per party
    //     $rentalHouseAggregates = DB::table('rental_house_party_maps as rhpm')
    //         ->select(
    //             'rhpm.rental_party_id',
    //             DB::raw("SUM(rhpm.monthly_rent) AS total_rent"),
    //             DB::raw("SUM(rhpm.security_money) AS security_money"),
    //             DB::raw("SUM(rhpm.remaining_security_money) AS remaining_security_money"),
    //             DB::raw("SUM(rhpm.refund_security_money) AS refund_security_money"),
    //             DB::raw("SUM(rhpm.auto_adjustment) AS auto_adjustment"),
    //             DB::raw("
    //             SUM(
    //                 CASE
    //                     WHEN rhpm.rent_start_date IS NOT NULL AND rhpm.monthly_rent IS NOT NULL AND rhpm.monthly_rent > 0
    //                     THEN
    //                         -- Handle YYYY-MM format by converting to first day of month
    //                         (
    //                             (YEAR('$currentDate') - YEAR(STR_TO_DATE(CONCAT(rhpm.rent_start_date, '-01'), '%Y-%m-%d'))) * 12
    //                             + (MONTH('$currentDate') - MONTH(STR_TO_DATE(CONCAT(rhpm.rent_start_date, '-01'), '%Y-%m-%d')))
    //                             + 1  -- Include the start month
    //                         ) * rhpm.monthly_rent
    //                     ELSE 0
    //                 END
    //             ) AS total_expected_rent
    //         ")
    //         )
    //         ->where('rhpm.status', 'active')
    //         ->groupBy('rhpm.rental_party_id');

    //     // Apply filters to rental house aggregates
    //     if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //         $rentalHouseAggregates->where('rhpm.rental_party_id', $filters['filter']['head_id']);
    //     }

    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $rentalHouseAggregates->where(function ($query) use ($filters) {
    //             $query->where('rhpm.rent_start_date', '<=', $filters['filter']['end_date'])
    //                 ->orWhereNull('rhpm.rent_start_date');
    //         });
    //     }

    //     // Subquery for party aggregates from rental_postings
    //     $partyPostingAggregates = DB::table('rental_postings')
    //         ->select(
    //             'head_id',
    //             DB::raw("SUM(CASE WHEN entry_type = 'rent_received' THEN amount_bdt ELSE 0 END) AS total_rent_received"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'auto_adjustment' THEN amount_bdt ELSE 0 END) AS total_auto_adjustment"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'security_money_refund' THEN amount_bdt ELSE 0 END) AS total_security_refund"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'security_money_amount' THEN amount_bdt ELSE 0 END) AS total_security_money_received")
    //         )
    //         ->where('status', 'approved');

    //     // Apply date filter to party aggregates if provided
    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $partyPostingAggregates->whereBetween('posting_date', [
    //             $filters['filter']['start_date'],
    //             $filters['filter']['end_date']
    //         ]);
    //     }

    //     $partyPostingAggregates->groupBy('head_id');

    //     // Debug: Check what's happening with the rental house aggregates
    //     $debugRentalData = DB::table('rental_house_party_maps')
    //         ->where('rental_party_id', 12)
    //         ->where('status', 'active')
    //         ->get();

    //     // Main query joining both aggregates
    //     $baseQuery = DB::table('rental_parties as rp')
    //         ->joinSub($rentalHouseAggregates, 'house_agg', function ($join) {
    //             $join->on('house_agg.rental_party_id', '=', 'rp.id');
    //         })
    //         ->leftJoinSub($partyPostingAggregates, 'posting_agg', function ($join) {
    //             $join->on('posting_agg.head_id', '=', 'rp.id');
    //         });

    //     // Total count for pagination
    //     $total = $baseQuery->count();

    //     // Pagination
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;

    //     $summaryData = $baseQuery
    //         ->select(
    //             'rp.id as party_id',
    //             'rp.party_name',
    //             'house_agg.total_rent',
    //             'house_agg.security_money',
    //             'house_agg.remaining_security_money',
    //             'house_agg.refund_security_money',
    //             'house_agg.auto_adjustment',
    //             'house_agg.total_expected_rent',
    //             DB::raw("COALESCE(posting_agg.total_auto_adjustment, 0) AS auto_adjust_amount"),
    //             DB::raw("COALESCE(posting_agg.total_auto_adjustment, 0) AS already_adjusted"),
    //             DB::raw("COALESCE(posting_agg.total_rent_received, 0) AS total_received"),
    //             DB::raw("COALESCE(posting_agg.total_security_refund, 0) AS refund_amount"),
    //             // Total receivable calculation with COALESCE to handle NULL
    //             DB::raw("COALESCE(house_agg.total_expected_rent, 0) - COALESCE(posting_agg.total_rent_received, 0) AS total_receivable")
    //         )
    //         ->orderBy('rp.party_name', 'ASC')
    //         ->offset(($page - 1) * $pageSize)
    //         ->limit($pageSize)
    //         ->get();

    //     // Calculate overall summary
    //     $overallSummary = DB::table('rental_parties as rp')
    //         ->joinSub($rentalHouseAggregates, 'house_agg', function ($join) {
    //             $join->on('house_agg.rental_party_id', '=', 'rp.id');
    //         })
    //         ->leftJoinSub($partyPostingAggregates, 'posting_agg', function ($join) {
    //             $join->on('posting_agg.head_id', '=', 'rp.id');
    //         })
    //         ->select(
    //             DB::raw("SUM(house_agg.total_rent) AS total_monthly_rent"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_rent_received, 0)) AS total_rent_received"),
    //             DB::raw("SUM(house_agg.security_money) AS total_security_money"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_auto_adjustment, 0)) AS total_auto_adjustment"),
    //             DB::raw("SUM(house_agg.remaining_security_money) AS total_remaining_security_money"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_security_refund, 0)) AS total_security_refund"),
    //             DB::raw("SUM(COALESCE(house_agg.total_expected_rent, 0) - COALESCE(posting_agg.total_rent_received, 0)) AS total_receivable")
    //         )
    //         ->first();

    //     return response()->json([
    //         'data' => $summaryData,
    //         'summary' => $overallSummary,
    //         'total' => $total,
    //         'debug' => [ // Remove this in production
    //             'rental_data_for_party_12' => $debugRentalData
    //         ]
    //     ]);
    // }


    // public function getRentalLedgerSummary(Request $request)
    // {
    //     $filters = $request->query();

    //     $currentDate = now();
    //     $currentYear = $currentDate->year;
    //     $currentMonth = $currentDate->month;

    //     // Subquery for rental houses
    //     $rentalHouseAggregates = DB::table('rental_house_party_maps as rhpm')
    //         ->select(
    //             'rhpm.rental_party_id',
    //             DB::raw("SUM(rhpm.monthly_rent) AS total_rent"),
    //             DB::raw("SUM(rhpm.security_money) AS security_money"),
    //             DB::raw("SUM(rhpm.remaining_security_money) AS remaining_security_money"),
    //             DB::raw("SUM(rhpm.refund_security_money) AS refund_security_money"),
    //             DB::raw("SUM(rhpm.auto_adjustment) AS auto_adjustment"),
    //             DB::raw("
    //                 SUM(
    //                     CASE 
    //                         WHEN rhpm.rent_start_date IS NOT NULL AND rhpm.monthly_rent IS NOT NULL AND rhpm.monthly_rent > 0
    //                         THEN 
    //                             (
    //                                 (YEAR('$currentDate') - YEAR(STR_TO_DATE(CONCAT(rhpm.rent_start_date, '-01'), '%Y-%m-%d'))) * 12 
    //                                 + (MONTH('$currentDate') - MONTH(STR_TO_DATE(CONCAT(rhpm.rent_start_date, '-01'), '%Y-%m-%d')))
    //                                 + 1
    //                             ) * rhpm.monthly_rent
    //                         ELSE 0 
    //                     END
    //                 ) AS total_expected_rent
    //             ")
    //         )
    //         ->where('rhpm.status', 'active')
    //         ->groupBy('rhpm.rental_party_id');

    //     if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //         $rentalHouseAggregates->where('rhpm.rental_party_id', $filters['filter']['head_id']);
    //     }

    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $rentalHouseAggregates->where(function ($query) use ($filters) {
    //             $query->where('rhpm.rent_start_date', '<=', $filters['filter']['end_date'])
    //                 ->orWhereNull('rhpm.rent_start_date');
    //         });
    //     }

    //     // Subquery for rental postings
    //     $partyPostingAggregates = DB::table('rental_postings')
    //         ->select(
    //             'head_id',
    //             DB::raw("SUM(CASE WHEN entry_type = 'rent_received' THEN amount_bdt ELSE 0 END) AS total_rent_received"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'auto_adjustment' THEN amount_bdt ELSE 0 END) AS total_auto_adjustment"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'security_money_refund' THEN amount_bdt ELSE 0 END) AS total_security_refund"),
    //             DB::raw("SUM(CASE WHEN entry_type = 'security_money_amount' THEN amount_bdt ELSE 0 END) AS total_security_money_received")
    //         )
    //         ->where('status', 'approved');

    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $partyPostingAggregates->whereBetween('posting_date', [
    //             $filters['filter']['start_date'],
    //             $filters['filter']['end_date']
    //         ]);
    //     }

    //     $partyPostingAggregates->groupBy('head_id');

    //     // Base query
    //     $baseQuery = DB::table('rental_parties as rp')
    //         ->joinSub($rentalHouseAggregates, 'house_agg', function ($join) {
    //             $join->on('house_agg.rental_party_id', '=', 'rp.id');
    //         })
    //         ->leftJoinSub($partyPostingAggregates, 'posting_agg', function ($join) {
    //             $join->on('posting_agg.head_id', '=', 'rp.id');
    //         });

    //     $total = $baseQuery->count();
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;

    //     $summaryData = $baseQuery
    //         ->select(
    //             'rp.id as party_id',
    //             'rp.party_name',
    //             'house_agg.total_rent',
    //             'house_agg.security_money',
    //             'house_agg.remaining_security_money',
    //             'house_agg.refund_security_money',
    //             'house_agg.auto_adjustment',
    //             'house_agg.total_expected_rent',
    //             DB::raw("COALESCE(posting_agg.total_auto_adjustment, 0) AS auto_adjust_amount"),
    //             DB::raw("COALESCE(posting_agg.total_auto_adjustment, 0) AS already_adjusted"),
    //             DB::raw("COALESCE(posting_agg.total_rent_received, 0) AS total_received"),
    //             DB::raw("COALESCE(posting_agg.total_security_refund, 0) AS refund_amount"),
    //             DB::raw("COALESCE(house_agg.total_expected_rent, 0) - COALESCE(posting_agg.total_rent_received, 0) AS total_receivable")
    //         )
    //         ->orderBy('rp.party_name', 'ASC')
    //         ->offset(($page - 1) * $pageSize)
    //         ->limit($pageSize)
    //         ->get();

    //     // 🧮 Add total_due_month & missing_months
    //     foreach ($summaryData as $party) {
    //         $startDate = DB::table('rental_house_party_maps')
    //             ->where('rental_party_id', $party->party_id)
    //             ->where('status', 'active')
    //             ->min('rent_start_date');

    //         if (!$startDate) {
    //             $party->total_due_month = 0;
    //             $party->missing_months = '';
    //             continue;
    //         }

    //         $start = \Carbon\Carbon::parse($startDate)->startOfMonth();
    //         $end = now()->startOfMonth();

    //         // expected months
    //         $expectedMonths = [];
    //         while ($start <= $end) {
    //             $expectedMonths[] = $start->format('M-y');
    //             $start->addMonth();
    //         }

    //         // months rent received
    //         $receivedMonths = DB::table('rental_postings')
    //             ->where('head_id', $party->party_id)
    //             ->where('entry_type', 'rent_received')
    //             ->where('status', 'approved')
    //             ->pluck('posting_date')
    //             ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M-y'))
    //             ->unique()
    //             ->toArray();

    //         $missing = array_diff($expectedMonths, $receivedMonths);

    //         $party->total_due_month = count($missing);
    //         $party->missing_months = implode(', ', $missing);
    //     }

    //     // overall summary
    //     $overallSummary = DB::table('rental_parties as rp')
    //         ->joinSub($rentalHouseAggregates, 'house_agg', function ($join) {
    //             $join->on('house_agg.rental_party_id', '=', 'rp.id');
    //         })
    //         ->leftJoinSub($partyPostingAggregates, 'posting_agg', function ($join) {
    //             $join->on('posting_agg.head_id', '=', 'rp.id');
    //         })
    //         ->select(
    //             DB::raw("SUM(house_agg.total_rent) AS total_monthly_rent"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_rent_received, 0)) AS total_rent_received"),
    //             DB::raw("SUM(house_agg.security_money) AS total_security_money"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_auto_adjustment, 0)) AS total_auto_adjustment"),
    //             DB::raw("SUM(house_agg.remaining_security_money) AS total_remaining_security_money"),
    //             DB::raw("SUM(COALESCE(posting_agg.total_security_refund, 0)) AS total_security_refund"),
    //             DB::raw("SUM(COALESCE(house_agg.total_expected_rent, 0) - COALESCE(posting_agg.total_rent_received, 0)) AS total_receivable"),
    //             DB::raw("SUM(COALESCE(house_agg.total_expected_rent, 0) - COALESCE(posting_agg.total_rent_received, 0)) AS total_due_amount")
    //         )
    //         ->first();

    //     return response()->json([
    //         'data' => $summaryData,
    //         'summary' => $overallSummary,
    //         'total' => $total,
    //     ]);
    // }



    public function getRentalLedgerSummary(Request $request)
    {
        $filters = $request->query();
        $currentDate = now();

        
        $rentalMappingsQuery = DB::table('rental_mappings as rm')
            ->select('rm.*');

        if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
            $rentalMappingsQuery->where('rm.party_id', $filters['filter']['head_id']);
        }

        if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
            $rentalMappingsQuery->where('rm.house_id', $filters['filter']['house_id']);
        }

        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $rentalMappingsQuery->where(function ($query) use ($filters) {
                $query->where('rm.rent_start_date', '<=', $filters['filter']['end_date'])
                    ->orWhereNull('rm.rent_start_date');
            });
        }

        $rentalMappings = $rentalMappingsQuery->get();

        
        $mappingsByPartyHouse = $rentalMappings->groupBy(function($item) {
            return $item->party_id . '_' . $item->house_id;
        });

        
        $rentalPostingsQuery = DB::table('rental_postings')
            ->where('status', 'approved');

        if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
            $rentalPostingsQuery->whereBetween('posting_date', [
                $filters['filter']['start_date'],
                $filters['filter']['end_date']
            ]);
        }

        if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
            $rentalPostingsQuery->where('house_id', $filters['filter']['house_id']);
        }

        $rentalPostings = $rentalPostingsQuery->get()->groupBy(function($item) {
            return $item->head_id . '_' . $item->house_id;
        });

        
        $summaryData = [];
        
        foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
            $firstMapping = $partyHouseMappings->first();
            $partyId = $firstMapping->party_id;
            $houseId = $firstMapping->house_id;
            
            $party = DB::table('rental_parties')->where('id', $partyId)->first();
            
            if (!$party) continue;

            
            $partyHousePostings = $rentalPostings->get($key, collect());

            
            $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

            
            $activeMapping = $sortedMappings->where('status', 'active')->last();
            $latestMapping = $activeMapping ? $activeMapping : $sortedMappings->last();
            $totalMonthlyRent = (float)$latestMapping->monthly_rent;

           
            $totalSecurityMoney = 0;
            $totalRemainingSecurityMoney = 0;
            $totalRefundSecurityMoney = 0;
            $totalAutoAdjustment = 0;
            $totalExpectedRent = 0;
            $totalRentReceived = 0;
            $totalAutoAdjustmentPosting = 0;
            $totalSecurityRefund = 0;

            
            foreach ($sortedMappings as $mapping) {
                $totalSecurityMoney += (float)$mapping->security_money;
                $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;
                $totalRefundSecurityMoney += (float)($mapping->refund_security_money ?? 0);
                $totalAutoAdjustment += (float)($mapping->auto_adjustment ?? 0);

               
                if ($mapping->rent_start_date && $mapping->monthly_rent) {
                    $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
                   
                    $endDate = $currentDate->copy()->startOfMonth();
                    if ($mapping->rent_end_date) {
                        $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
                        if ($mappingEndDate->lt($endDate)) {
                            $endDate = $mappingEndDate;
                        }
                    }

                    
                    if ($startDate->lte($endDate)) {
                        $months = $startDate->diffInMonths($endDate) + 1;
                        $totalExpectedRent += $months * (float)$mapping->monthly_rent;
                    }
                }
            }

            
            foreach ($partyHousePostings as $posting) {
                if ($posting->entry_type === 'rent_received') {
                    $totalRentReceived += (float)$posting->amount_bdt;
                } elseif ($posting->entry_type === 'auto_adjustment') {
                    $totalAutoAdjustmentPosting += (float)$posting->amount_bdt;
                } elseif ($posting->entry_type === 'security_money_refund') {
                    $totalSecurityRefund += (float)$posting->amount_bdt;
                }
            }

           
            $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
            
           
            if ($totalReceivable == 0) {
                
                $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
            } else {
                $totalDueAmount = $totalReceivable;
            }

            
            $totalDueMonth = 0;
            $partialDueAmount = 0;
            
            if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
                $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
                $totalDueMonth = $fullMonths;
                $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
                
                if ($partialDueAmount > 0.01) {
                    $totalDueMonth += 1;
                } else {
                    $partialDueAmount = 0;
                }
            }

            $startDates = $partyHouseMappings->pluck('rent_start_date')->filter()->toArray();
            $missingMonths = '';
            
            if (!empty($startDates)) {
                $earliestStartDate = min($startDates);
                $start = \Carbon\Carbon::parse($earliestStartDate)->startOfMonth();
                
                $end = now()->startOfMonth();
                
                $hasActiveMapping = $sortedMappings->where('status', 'active')->isNotEmpty();
                
                if (!$hasActiveMapping) {
                    
                    $endDates = $partyHouseMappings->pluck('rent_end_date')->filter()->toArray();
                    if (!empty($endDates)) {
                        $latestEndDate = max($endDates);
                        $end = \Carbon\Carbon::parse($latestEndDate)->startOfMonth();
                    }
                }

                $expectedMonths = [];
                $current = $start->copy();
                while ($current <= $end) {
                    $expectedMonths[] = $current->format('M-y');
                    $current->addMonth();
                }

                $receivedMonthsQuery = DB::table('rental_postings')
                    ->where('head_id', $partyId)
                    ->where('house_id', $houseId)
                    ->where('entry_type', 'rent_received')
                    ->where('status', 'approved');

                $receivedMonths = $receivedMonthsQuery
                    ->get()
                    ->map(function ($row) {
                        if (!empty($row->rent_received)) {
                            try {
                                return \Carbon\Carbon::createFromFormat('Y-m-d', $row->rent_received . '-01')
                                    ->format('M-y');
                            } catch (\Exception $e) {
                                // fallback to posting_date below
                            }
                        }

                        if (!empty($row->posting_date)) {
                            try {
                                return \Carbon\Carbon::parse($row->posting_date)
                                    ->startOfMonth()
                                    ->format('M-y');
                            } catch (\Exception $e) {
                                return null;
                            }
                        }

                        return null;
                    })
                    ->filter()
                    ->unique()
                    ->toArray();

                $missing = array_diff($expectedMonths, $receivedMonths);
                $missingArray = array_values($missing);
                rsort($missingArray);
                $missingMonths = implode(', ', array_slice($missingArray, 0, $totalDueMonth));
            }

            $summaryData[] = (object)[
                'party_id' => $partyId,
                'house_id' => $houseId,
                'party_name' => $party->party_name,
                'total_rent' => number_format($totalMonthlyRent, 2, '.', ''),
                'security_money' => number_format($totalSecurityMoney, 2, '.', ''),
                'remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
                'refund_security_money' => number_format($totalRefundSecurityMoney, 2, '.', ''),
                'auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
                'total_expected_rent' => $totalExpectedRent,
                'auto_adjust_amount' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
                'already_adjusted' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
                'total_received' => number_format($totalRentReceived, 2, '.', ''),
                'refund_amount' => number_format($totalSecurityRefund, 2, '.', ''),
                'total_receivable' => number_format($totalReceivable, 2, '.', ''),
                'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
                'total_due_month' => (int)$totalDueMonth,
                'partial_due_amount' => round($partialDueAmount, 2),
                'missing_months' => $missingMonths,
            ];
        }

        
        usort($summaryData, function($a, $b) {
            return strcmp($a->party_name, $b->party_name);
        });

        
        $total = count($summaryData);
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['pageSize'] ?? 10;
        $summaryData = array_slice($summaryData, ($page - 1) * $pageSize, $pageSize);

        
        $overallSummary = $this->calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $currentDate, $filters);

        return response()->json([
            'data' => $summaryData,
            'summary' => $overallSummary,
            'total' => $total,
        ]);
    }

    private function calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $currentDate, $filters)
    {
        $totalMonthlyRent = 0;
        $totalRentReceived = 0;
        $totalSecurityMoney = 0;
        $totalAutoAdjustment = 0;
        $totalRemainingSecurityMoney = 0;
        $totalSecurityRefund = 0;
        $totalExpectedRent = 0;
        $totalReceivable = 0;
        $totalDueAmount = 0;

        foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
            $firstMapping = $partyHouseMappings->first();
            $partyId = $firstMapping->party_id;
            $houseId = $firstMapping->house_id;

            $partyHousePostings = $rentalPostings->get($key, collect());

            
            $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

           
            $activeMappings = $sortedMappings->where('status', 'active');
            if ($activeMappings->isNotEmpty()) {
               
                $latestActiveMapping = $activeMappings->last();
                $totalMonthlyRent += (float)$latestActiveMapping->monthly_rent;
            }
            

            
            foreach ($sortedMappings as $mapping) {
                $totalSecurityMoney += (float)$mapping->security_money;
                $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;

                
                if ($mapping->rent_start_date && $mapping->monthly_rent) {
                    $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
                    $endDate = $currentDate->copy()->startOfMonth();
                    if ($mapping->rent_end_date) {
                        $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
                        if ($mappingEndDate->lt($endDate)) {
                            $endDate = $mappingEndDate;
                        }
                    }

                    if ($startDate->lte($endDate)) {
                        $months = $startDate->diffInMonths($endDate) + 1;
                        $totalExpectedRent += $months * (float)$mapping->monthly_rent;
                    }
                }
            }

           
            foreach ($partyHousePostings as $posting) {
                if ($posting->entry_type === 'rent_received') {
                    $totalRentReceived += (float)$posting->amount_bdt;
                } elseif ($posting->entry_type === 'auto_adjustment') {
                    $totalAutoAdjustment += (float)$posting->amount_bdt;
                } elseif ($posting->entry_type === 'security_money_refund') {
                    $totalSecurityRefund += (float)$posting->amount_bdt;
                }
            }
        }

        
        $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
        if ($totalReceivable == 0 && $totalMonthlyRent > 0) {
            $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
        } else {
            $totalDueAmount = $totalReceivable;
        }

        
        $totalDueMonth = 0;
        $partialDueAmount = 0;
        
        if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
            $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
            $totalDueMonth = $fullMonths;
            $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
            if ($partialDueAmount > 0.01) {
                $totalDueMonth += 1;
            } else {
                $partialDueAmount = 0;
            }
        }

        return (object)[
            'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
            'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
            'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
            'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
            'total_remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
            'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
            'total_receivable' => number_format($totalReceivable, 2, '.', ''),
            'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
            'total_due_month' => (int)$totalDueMonth,
            'partial_due_amount' => round($partialDueAmount, 2),
            'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
        ];
    }





    // public function getRentalLedgerSummary(Request $request)
    // {
    //     $filters = $request->query();
    //     $currentDate = now();

        
    //     $rentalMappingsQuery = DB::table('rental_mappings as rm')
    //         ->select('rm.*');

    //     if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //         $rentalMappingsQuery->where('rm.party_id', $filters['filter']['head_id']);
    //     }

    //     if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
    //         $rentalMappingsQuery->where('rm.house_id', $filters['filter']['house_id']);
    //     }

    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $rentalMappingsQuery->where(function ($query) use ($filters) {
    //             $query->where('rm.rent_start_date', '<=', $filters['filter']['end_date'])
    //                 ->orWhereNull('rm.rent_start_date');
    //         });
    //     }

    //     $rentalMappings = $rentalMappingsQuery->get();

        
    //     $mappingsByPartyHouse = $rentalMappings->groupBy(function($item) {
    //         return $item->party_id . '_' . $item->house_id;
    //     });

        
    //     $rentalPostingsQuery = DB::table('rental_postings')
    //         ->where('status', 'approved');

    //     if (isset($filters['filter']['start_date']) && isset($filters['filter']['end_date'])) {
    //         $rentalPostingsQuery->whereBetween('posting_date', [
    //             $filters['filter']['start_date'],
    //             $filters['filter']['end_date']
    //         ]);
    //     }

    //     if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
    //         $rentalPostingsQuery->where('house_id', $filters['filter']['house_id']);
    //     }

    //     $rentalPostings = $rentalPostingsQuery->get()->groupBy(function($item) {
    //         return $item->head_id . '_' . $item->house_id;
    //     });

        
    //     $summaryData = [];
        
    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $firstMapping = $partyHouseMappings->first();
    //         $partyId = $firstMapping->party_id;
    //         $houseId = $firstMapping->house_id;
            
    //         $party = DB::table('rental_parties')->where('id', $partyId)->first();
            
    //         if (!$party) continue;

            
    //         $partyHousePostings = $rentalPostings->get($key, collect());

            
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

            
    //         $activeMapping = $sortedMappings->where('status', 'active')->last();
    //         $latestMapping = $activeMapping ? $activeMapping : $sortedMappings->last();
    //         $totalMonthlyRent = (float)$latestMapping->monthly_rent;

           
    //         $totalSecurityMoney = 0;
    //         $totalRemainingSecurityMoney = 0;
    //         $totalRefundSecurityMoney = 0;
    //         $totalAutoAdjustment = 0;
    //         $totalExpectedRent = 0;
    //         $totalRentReceived = 0;
    //         $totalAutoAdjustmentPosting = 0;
    //         $totalSecurityRefund = 0;

            
    //         foreach ($sortedMappings as $mapping) {
    //             $totalSecurityMoney += (float)$mapping->security_money;
    //             $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;
    //             $totalRefundSecurityMoney += (float)($mapping->refund_security_money ?? 0);
    //             $totalAutoAdjustment += (float)($mapping->auto_adjustment ?? 0);

               
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
                   
    //                 $endDate = $currentDate->copy()->startOfMonth();
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDate)) {
    //                         $endDate = $mappingEndDate;
    //                     }
    //                 }

                    
    //                 if ($startDate->lte($endDate)) {
    //                     $months = $startDate->diffInMonths($endDate) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }

            
    //         foreach ($partyHousePostings as $posting) {
    //             if ($posting->entry_type === 'rent_received') {
    //                 $totalRentReceived += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'auto_adjustment') {
    //                 $totalAutoAdjustmentPosting += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'security_money_refund') {
    //                 $totalSecurityRefund += (float)$posting->amount_bdt;
    //             }
    //         }

           
    //         $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
            
           
    //         if ($totalReceivable == 0) {
                
    //             $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
    //         } else {
    //             $totalDueAmount = $totalReceivable;
    //         }

            
    //         $totalDueMonth = 0;
    //         $partialDueAmount = 0;
            
    //         if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //             $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //             $totalDueMonth = $fullMonths;
    //             $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
                
    //             if ($partialDueAmount > 0.01) {
    //                 $totalDueMonth += 1;
    //             } else {
    //                 $partialDueAmount = 0;
    //             }
    //         }

    //         $startDates = $partyHouseMappings->pluck('rent_start_date')->filter()->toArray();
    //         $missingMonths = '';
            
    //         if (!empty($startDates)) {
    //             $earliestStartDate = min($startDates);
    //             $start = \Carbon\Carbon::parse($earliestStartDate)->startOfMonth();
                
    //             $end = now()->startOfMonth();
                
    //             $hasActiveMapping = $sortedMappings->where('status', 'active')->isNotEmpty();
                
    //             if (!$hasActiveMapping) {
                    
    //                 $endDates = $partyHouseMappings->pluck('rent_end_date')->filter()->toArray();
    //                 if (!empty($endDates)) {
    //                     $latestEndDate = max($endDates);
    //                     $end = \Carbon\Carbon::parse($latestEndDate)->startOfMonth();
    //                 }
    //             }

    //             $expectedMonths = [];
    //             $current = $start->copy();
    //             while ($current <= $end) {
    //                 $expectedMonths[] = $current->format('M-y');
    //                 $current->addMonth();
    //             }

    //             $receivedMonthsQuery = DB::table('rental_postings')
    //                 ->where('head_id', $partyId)
    //                 ->where('house_id', $houseId)
    //                 ->where('entry_type', 'rent_received')
    //                 ->where('status', 'approved');

    //             $receivedMonths = $receivedMonthsQuery
    //             ->pluck('rent_received')
    //             ->map(function($rentReceived) {
    //                 if ($rentReceived) {
    //                     try {
    //                         $date = \Carbon\Carbon::createFromFormat('Y-m', $rentReceived);
    //                         return $date->format('M-y');
    //                     } catch (\Exception $e) {
    //                         return null;
    //                     }
    //                 }
    //                 return null;
    //             })
    //             ->filter()
    //             ->unique()
    //             ->toArray();

    //             $missing = array_diff($expectedMonths, $receivedMonths);
    //             $missingMonths = implode(', ', $missing);
    //         }

    //         $summaryData[] = (object)[
    //             'party_id' => $partyId,
    //             'house_id' => $houseId,
    //             'party_name' => $party->party_name,
    //             'total_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //             'security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //             'remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
    //             'refund_security_money' => number_format($totalRefundSecurityMoney, 2, '.', ''),
    //             'auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //             'total_expected_rent' => $totalExpectedRent,
    //             'auto_adjust_amount' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
    //             'already_adjusted' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
    //             'total_received' => number_format($totalRentReceived, 2, '.', ''),
    //             'refund_amount' => number_format($totalSecurityRefund, 2, '.', ''),
    //             'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //             'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //             'total_due_month' => (int)$totalDueMonth,
    //             'partial_due_amount' => round($partialDueAmount, 2),
    //             'missing_months' => $missingMonths,
    //         ];
    //     }

        
    //     usort($summaryData, function($a, $b) {
    //         return strcmp($a->party_name, $b->party_name);
    //     });

        
    //     $total = count($summaryData);
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;
    //     $summaryData = array_slice($summaryData, ($page - 1) * $pageSize, $pageSize);

        
    //     $overallSummary = $this->calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $currentDate, $filters);

    //     return response()->json([
    //         'data' => $summaryData,
    //         'summary' => $overallSummary,
    //         'total' => $total,
    //     ]);
    // }

    // private function calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $currentDate, $filters)
    // {
    //     $totalMonthlyRent = 0;
    //     $totalRentReceived = 0;
    //     $totalSecurityMoney = 0;
    //     $totalAutoAdjustment = 0;
    //     $totalRemainingSecurityMoney = 0;
    //     $totalSecurityRefund = 0;
    //     $totalExpectedRent = 0;
    //     $totalReceivable = 0;
    //     $totalDueAmount = 0;

    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $firstMapping = $partyHouseMappings->first();
    //         $partyId = $firstMapping->party_id;
    //         $houseId = $firstMapping->house_id;

    //         $partyHousePostings = $rentalPostings->get($key, collect());

            
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

           
    //         $activeMappings = $sortedMappings->where('status', 'active');
    //         if ($activeMappings->isNotEmpty()) {
               
    //             $latestActiveMapping = $activeMappings->last();
    //             $totalMonthlyRent += (float)$latestActiveMapping->monthly_rent;
    //         }
            

            
    //         foreach ($sortedMappings as $mapping) {
    //             $totalSecurityMoney += (float)$mapping->security_money;
    //             $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;

                
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDate = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
                    
    //                 $endDate = $currentDate->copy()->startOfMonth();
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDate)) {
    //                         $endDate = $mappingEndDate;
    //                     }
    //                 }

    //                 if ($startDate->lte($endDate)) {
    //                     $months = $startDate->diffInMonths($endDate) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }

           
    //         foreach ($partyHousePostings as $posting) {
    //             if ($posting->entry_type === 'rent_received') {
    //                 $totalRentReceived += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'auto_adjustment') {
    //                 $totalAutoAdjustment += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'security_money_refund') {
    //                 $totalSecurityRefund += (float)$posting->amount_bdt;
    //             }
    //         }
    //     }

        
    //     $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
    //     if ($totalReceivable == 0 && $totalMonthlyRent > 0) {
    //         $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
    //     } else {
    //         $totalDueAmount = $totalReceivable;
    //     }

        
    //     $totalDueMonth = 0;
    //     $partialDueAmount = 0;
        
    //     if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //         $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //         $totalDueMonth = $fullMonths;
    //         $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
    //         if ($partialDueAmount > 0.01) {
    //             $totalDueMonth += 1;
    //         } else {
    //             $partialDueAmount = 0;
    //         }
    //     }

    //     return (object)[
    //         'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //         'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
    //         'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //         'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //         'total_remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
    //         'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
    //         'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //         'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //         'total_due_month' => (int)$totalDueMonth,
    //         'partial_due_amount' => round($partialDueAmount, 2),
    //         'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
    //     ];
    // }




    // public function getRentalLedgerSummary(Request $request)
    // {
    //     $filters = $request->query();
    //     $currentDate = now();

       
    //     $defaultStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    //     $defaultEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        
    //     $startDate = $filters['filter']['start_date'] ?? $defaultStartDate;
    //     $endDate = $filters['filter']['end_date'] ?? $defaultEndDate;
        
       
    //     $usingDefaultDates = !isset($filters['filter']['start_date']) && !isset($filters['filter']['end_date']);

        
    //     $rentalMappingsQuery = DB::table('rental_mappings as rm')
    //         ->select('rm.*');

    //     if (isset($filters['filter']['head_id']) && $filters['filter']['head_id'] !== '') {
    //         $rentalMappingsQuery->where('rm.party_id', $filters['filter']['head_id']);
    //     }

    //     if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
    //         $rentalMappingsQuery->where('rm.house_id', $filters['filter']['house_id']);
    //     }

       
    //     $rentalMappingsQuery->where(function ($query) use ($endDate) {
    //         $query->where('rm.rent_start_date', '<=', $endDate)
    //             ->orWhereNull('rm.rent_start_date');
    //     });

    //     $rentalMappings = $rentalMappingsQuery->get();

    //     // Group by party and house
    //     $mappingsByPartyHouse = $rentalMappings->groupBy(function($item) {
    //         return $item->party_id . '_' . $item->house_id;
    //     });

    //     // Rental Postings Query
    //     $rentalPostingsQuery = DB::table('rental_postings')
    //         ->where('status', 'approved');

        
    //     $rentalPostingsQuery->whereBetween('posting_date', [$startDate, $endDate]);

    //     if (isset($filters['filter']['house_id']) && $filters['filter']['house_id'] !== '') {
    //         $rentalPostingsQuery->where('house_id', $filters['filter']['house_id']);
    //     }

    //     $rentalPostings = $rentalPostingsQuery->get()->groupBy(function($item) {
    //         return $item->head_id . '_' . $item->house_id;
    //     });

    //     // Summary Data
    //     $summaryData = [];
        
    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $firstMapping = $partyHouseMappings->first();
    //         $partyId = $firstMapping->party_id;
    //         $houseId = $firstMapping->house_id;
            
    //         $party = DB::table('rental_parties')->where('id', $partyId)->first();
            
    //         if (!$party) continue;

    //         // Get postings for this party and house
    //         $partyHousePostings = $rentalPostings->get($key, collect());

    //         // Sort mappings by rent_start_date
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

    //         // Get active or latest mapping
    //         $activeMapping = $sortedMappings->where('status', 'active')->last();
    //         $latestMapping = $activeMapping ? $activeMapping : $sortedMappings->last();
    //         $totalMonthlyRent = (float)$latestMapping->monthly_rent;

    //         // Calculate totals
    //         $totalSecurityMoney = 0;
    //         $totalRemainingSecurityMoney = 0;
    //         $totalRefundSecurityMoney = 0;
    //         $totalAutoAdjustment = 0;
    //         $totalExpectedRent = 0;
    //         $totalRentReceived = 0;
    //         $totalAutoAdjustmentPosting = 0;
    //         $totalSecurityRefund = 0;

    //         foreach ($sortedMappings as $mapping) {
    //             $totalSecurityMoney += (float)$mapping->security_money;
    //             $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;
    //             $totalRefundSecurityMoney += (float)($mapping->refund_security_money ?? 0);
    //             $totalAutoAdjustment += (float)($mapping->auto_adjustment ?? 0);

    //             // Calculate expected rent based on date range
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDateObj = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
    //                 $endDateObj = \Carbon\Carbon::parse($endDate)->startOfMonth(); // Use provided end date
                    
    //                 // If mapping has an end date, use the earlier of the two
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDateObj)) {
    //                         $endDateObj = $mappingEndDate;
    //                     }
    //                 }

    //                 // Ensure start date is not after end date
    //                 if ($startDateObj->lte($endDateObj)) {
    //                     $months = $startDateObj->diffInMonths($endDateObj) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }

    //         // Calculate received amounts from postings (already filtered by date range)
    //         foreach ($partyHousePostings as $posting) {
    //             if ($posting->entry_type === 'rent_received') {
    //                 $totalRentReceived += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'auto_adjustment') {
    //                 $totalAutoAdjustmentPosting += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'security_money_refund') {
    //                 $totalSecurityRefund += (float)$posting->amount_bdt;
    //             }
    //         }

    //         // Calculate receivable amount
    //         $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
            
    //         // Calculate due amount
    //         if ($totalReceivable == 0) {
    //             $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
    //         } else {
    //             $totalDueAmount = $totalReceivable;
    //         }

    //         // Calculate due months and partial amount
    //         $totalDueMonth = 0;
    //         $partialDueAmount = 0;
            
    //         if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //             $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //             $totalDueMonth = $fullMonths;
    //             $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
                
    //             if ($partialDueAmount > 0.01) {
    //                 $totalDueMonth += 1;
    //             } else {
    //                 $partialDueAmount = 0;
    //             }
    //         }

    //         // Calculate missing months
    //         $startDates = $partyHouseMappings->pluck('rent_start_date')->filter()->toArray();
    //         $missingMonths = '';
            
    //         if (!empty($startDates)) {
    //             $earliestStartDate = min($startDates);
    //             $start = \Carbon\Carbon::parse($earliestStartDate)->startOfMonth();
                
    //             // Use the provided end date or current date
    //             $end = \Carbon\Carbon::parse($endDate)->startOfMonth();
                
    //             $hasActiveMapping = $sortedMappings->where('status', 'active')->isNotEmpty();
                
    //             if (!$hasActiveMapping) {
    //                 $endDates = $partyHouseMappings->pluck('rent_end_date')->filter()->toArray();
    //                 if (!empty($endDates)) {
    //                     $latestEndDate = max($endDates);
    //                     $end = \Carbon\Carbon::parse($latestEndDate)->startOfMonth();
    //                 }
    //             }

    //             $expectedMonths = [];
    //             $current = $start->copy();
    //             while ($current <= $end) {
    //                 $expectedMonths[] = $current->format('M-y');
    //                 $current->addMonth();
    //             }

    //             $receivedMonthsQuery = DB::table('rental_postings')
    //                 ->where('head_id', $partyId)
    //                 ->where('house_id', $houseId)
    //                 ->where('entry_type', 'rent_received')
    //                 ->where('status', 'approved')
    //                 ->whereBetween('posting_date', [$startDate, $endDate]); // Filter by date range

    //             $receivedMonths = $receivedMonthsQuery
    //                 ->pluck('rent_received')
    //                 ->map(function($rentReceived) {
    //                     if ($rentReceived) {
    //                         try {
    //                             $date = \Carbon\Carbon::createFromFormat('Y-m', $rentReceived);
    //                             return $date->format('M-y');
    //                         } catch (\Exception $e) {
    //                             return null;
    //                         }
    //                     }
    //                     return null;
    //                 })
    //                 ->filter()
    //                 ->unique()
    //                 ->toArray();

    //             $missing = array_diff($expectedMonths, $receivedMonths);
    //             $missingMonths = implode(', ', $missing);
    //         }

    //         $summaryData[] = (object)[
    //             'party_id' => $partyId,
    //             'house_id' => $houseId,
    //             'party_name' => $party->party_name,
    //             'total_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //             'security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //             'remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
    //             'refund_security_money' => number_format($totalRefundSecurityMoney, 2, '.', ''),
    //             'auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //             'total_expected_rent' => number_format($totalExpectedRent, 2, '.', ''),
    //             'auto_adjust_amount' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
    //             'already_adjusted' => number_format($totalAutoAdjustmentPosting, 2, '.', ''),
    //             'total_received' => number_format($totalRentReceived, 2, '.', ''),
    //             'refund_amount' => number_format($totalSecurityRefund, 2, '.', ''),
    //             'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //             'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //             'total_due_month' => (int)$totalDueMonth,
    //             'partial_due_amount' => round($partialDueAmount, 2),
    //             'missing_months' => $missingMonths,
    //         ];
    //     }

    //     // Sort by party name
    //     usort($summaryData, function($a, $b) {
    //         return strcmp($a->party_name, $b->party_name);
    //     });

    //     // Pagination
    //     $total = count($summaryData);
    //     $page = $filters['page'] ?? 1;
    //     $pageSize = $filters['pageSize'] ?? 10;
    //     $summaryData = array_slice($summaryData, ($page - 1) * $pageSize, $pageSize);

    //     // Overall Summary
    //     $overallSummary = $this->calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $startDate, $endDate, $usingDefaultDates);

    //     return response()->json([
    //         'data' => $summaryData,
    //         'summary' => $overallSummary,
    //         'total' => $total,
    //         'date_range' => [
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'is_default_range' => $usingDefaultDates
    //         ]
    //     ]);
    // }

    // private function calculateOverallSummaryFromData($mappingsByPartyHouse, $rentalPostings, $startDate, $endDate, $usingDefaultDates)
    // {
    //     $totalMonthlyRent = 0;
    //     $totalRentReceived = 0;
    //     $totalSecurityMoney = 0;
    //     $totalAutoAdjustment = 0;
    //     $totalRemainingSecurityMoney = 0;
    //     $totalSecurityRefund = 0;
    //     $totalExpectedRent = 0;
    //     $totalReceivable = 0;
    //     $totalDueAmount = 0;

    //     foreach ($mappingsByPartyHouse as $key => $partyHouseMappings) {
    //         $firstMapping = $partyHouseMappings->first();
    //         $partyId = $firstMapping->party_id;
    //         $houseId = $firstMapping->house_id;

    //         $partyHousePostings = $rentalPostings->get($key, collect());

    //         // Sort mappings by rent_start_date
    //         $sortedMappings = $partyHouseMappings->sortBy('rent_start_date');

    //         // Get active mappings
    //         $activeMappings = $sortedMappings->where('status', 'active');
    //         if ($activeMappings->isNotEmpty()) {
    //             $latestActiveMapping = $activeMappings->last();
    //             $totalMonthlyRent += (float)$latestActiveMapping->monthly_rent;
    //         }

    //         // Calculate totals for each mapping
    //         foreach ($sortedMappings as $mapping) {
    //             $totalSecurityMoney += (float)$mapping->security_money;
    //             $totalRemainingSecurityMoney += (float)$mapping->remaining_security_money;

    //             // Calculate expected rent based on date range
    //             if ($mapping->rent_start_date && $mapping->monthly_rent) {
    //                 $startDateObj = \Carbon\Carbon::parse($mapping->rent_start_date)->startOfMonth();
    //                 $endDateObj = \Carbon\Carbon::parse($endDate)->startOfMonth();
                    
    //                 if ($mapping->rent_end_date) {
    //                     $mappingEndDate = \Carbon\Carbon::parse($mapping->rent_end_date)->startOfMonth();
    //                     if ($mappingEndDate->lt($endDateObj)) {
    //                         $endDateObj = $mappingEndDate;
    //                     }
    //                 }

    //                 if ($startDateObj->lte($endDateObj)) {
    //                     $months = $startDateObj->diffInMonths($endDateObj) + 1;
    //                     $totalExpectedRent += $months * (float)$mapping->monthly_rent;
    //                 }
    //             }
    //         }

    //         // Calculate received amounts from postings (already filtered by date range)
    //         foreach ($partyHousePostings as $posting) {
    //             if ($posting->entry_type === 'rent_received') {
    //                 $totalRentReceived += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'auto_adjustment') {
    //                 $totalAutoAdjustment += (float)$posting->amount_bdt;
    //             } elseif ($posting->entry_type === 'security_money_refund') {
    //                 $totalSecurityRefund += (float)$posting->amount_bdt;
    //             }
    //         }
    //     }

    //     // Calculate receivable amount
    //     $totalReceivable = max(0, $totalExpectedRent - $totalRentReceived);
        
    //     // Calculate due amount
    //     if ($totalReceivable == 0 && $totalMonthlyRent > 0) {
    //         $totalDueAmount = max(0, $totalMonthlyRent - $totalRentReceived);
    //     } else {
    //         $totalDueAmount = $totalReceivable;
    //     }

    //     // Calculate due months and partial amount
    //     $totalDueMonth = 0;
    //     $partialDueAmount = 0;
        
    //     if ($totalDueAmount > 0 && $totalMonthlyRent > 0) {
    //         $fullMonths = (int)($totalDueAmount / $totalMonthlyRent);
    //         $totalDueMonth = $fullMonths;
    //         $partialDueAmount = fmod($totalDueAmount, $totalMonthlyRent);
            
    //         if ($partialDueAmount > 0.01) {
    //             $totalDueMonth += 1;
    //         } else {
    //             $partialDueAmount = 0;
    //         }
    //     }

    //     return (object)[
    //         'total_monthly_rent' => number_format($totalMonthlyRent, 2, '.', ''),
    //         'total_rent_received' => number_format($totalRentReceived, 2, '.', ''),
    //         'total_security_money' => number_format($totalSecurityMoney, 2, '.', ''),
    //         'total_auto_adjustment' => number_format($totalAutoAdjustment, 2, '.', ''),
    //         'total_remaining_security_money' => number_format($totalRemainingSecurityMoney, 2, '.', ''),
    //         'total_security_refund' => number_format($totalSecurityRefund, 2, '.', ''),
    //         'total_expected_rent' => number_format($totalExpectedRent, 2, '.', ''),
    //         'total_receivable' => number_format($totalReceivable, 2, '.', ''),
    //         'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
    //         'total_due_month' => (int)$totalDueMonth,
    //         'partial_due_amount' => round($partialDueAmount, 2),
    //         'already_adjusted' => number_format($totalAutoAdjustment, 2, '.', ''),
    //         'date_range' => [
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'is_default_range' => $usingDefaultDates
    //         ]
    //     ];
    // }



    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $status = $request->input('status');

        if (empty($status) && $status !== 'all') {
            $status = 'pending';
        }

        $query = DB::table('rental_postings as rp')
            ->join('rental_parties as rpy', 'rpy.id', '=', 'rp.head_id')
            ->join('rental_houses as rh', 'rh.id', '=', 'rp.house_id')
            // ->join('rental_house_party_maps as rhpm', 'rhpm.rental_house_id', '=', 'rh.id')
            ->join('rental_mappings as rhpm', function ($join) {
                $join->on('rhpm.house_id', '=', 'rh.id')
                    ->on('rhpm.party_id', '=', 'rpy.id');
            })
            ->join('payment_channel_details as pcd', 'pcd.id', '=', 'rp.payment_channel_id')
            ->join('account_numbers as ac', 'ac.id', '=', 'rp.account_id')
            ->where('rhpm.status', 'active')
            ->select(
                'rp.*',
                'rp.rent_received',
                'rpy.party_name',
                'rpy.id as party_id',
                'rhpm.security_money',
                'rhpm.auto_adjustment',
                'rhpm.remaining_security_money',
                'rhpm.monthly_rent',
                'rhpm.refund_security_money',
                'rh.house_name',
                'pcd.method_name',
                'ac.ac_name',
                'ac.ac_no'
            );




        if ($status !== 'all') {
            $query->where('rp.status', $status);
        }
        $query->orderBy('rp.id', 'desc');
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



    public function store(Request $request)
    {


        $houseId = $request->input('house_id');
        $selectedMonth = $request->input('rent_received');

        if ($request->rent_received) {
            // $party = RentalMapping::where('party_id', $request->head_id)
            // ->where('house_id', $request->house_id)
            // ->where('status', 'active')
            // ->where('rent_start_date', $request->rent_received)->first();

            $party = RentalMapping::where('party_id', $request->head_id)
              ->where('house_id', $request->house_id)
              ->where('status', 'active')

              // rent_start_date <= selected month
              ->whereRaw("STR_TO_DATE(CONCAT(rent_start_date, '-01'), '%Y-%m-%d') <= STR_TO_DATE(CONCAT(?, '-01'), '%Y-%m-%d')", [$selectedMonth])

              // rent_end_date is null, empty, or >= selected month
              ->where(function($q) use ($selectedMonth) {
                  $q->whereNull('rent_end_date')
                    ->orWhere('rent_end_date', '')
                    ->orWhereRaw("STR_TO_DATE(CONCAT(rent_end_date, '-01'), '%Y-%m-%d') >= STR_TO_DATE(CONCAT(?, '-01'), '%Y-%m-%d')", [$selectedMonth]);
              })
              ->first();
        } else {
            $party = RentalMapping::where('party_id', $request->head_id)
            ->where('house_id', $request->house_id)
            ->where('status', 'active')
            ->latest('id')->first();

        }

        if (!$party) {
            return response()->json([
            'status' => false,
            'message' => 'No party mapping found for this house.',
            'data' => null,
            ], 500);
        }

        $monthlyRent = $party->monthly_rent;
        $autoAdjustment = $party->auto_adjustment;
        $securityMoney = $party->security_money;


        $existing = RentalPosting::where('head_id', $request->head_id)
        ->where('house_id', $request->house_id)
        ->where('status', 'approved')
        ->where('rent_received', $request->input('rent_received'))
        ->first();


        if($request->input('transaction_type') === 'rent_received'){
           if ($existing && $existing->amount_bdt + $request->amount_bdt  >= $monthlyRent) {

            return response()->json([
            'status' => false,
            'message' => 'Posting already exists.',
            'data' => null
            ], 500);
        }
        }
       





        $mapping = [
            'rent_received'          => 'received',
            'security_money_refund'  => 'payment',
            'auto_adjustment'        => 'payment',
            'other_amount'           => 'received',
            'security_money_amount'  => 'received',
        ];

        try {
            DB::beginTransaction();

            $transactionType = $request->input('transaction_type');
            $finalAmount = $monthlyRent;

            /**
             * Only apply auto-adjustment if transaction type is 'rent_received'
             */
            if ($transactionType === 'rent_received') {
                if ($autoAdjustment > 0 && $autoAdjustment <= $securityMoney) {

                    $finalAmount = $monthlyRent - $autoAdjustment;

                    $party->remaining_security_money -= $autoAdjustment;
                    $party->save();

                    // Create auto adjustment posting
                    RentalPosting::create([
                        'transaction_type'   => $mapping['auto_adjustment'],
                        'head_id'            => $request->input('head_id'),
                        'house_id'            => $request->input('house_id'),
                        'payment_channel_id' => $request->input('payment_channel_id'),
                        'account_id'         => $request->input('account_id'),
                        'receipt_number'     => $request->input('receipt_number'),
                        'amount_bdt'         => $autoAdjustment,
                        'posting_date'       => $request->input('posting_date'),
                        'rent_received'       => $request->input('rent_received'),
                        'note'               => $request->input('note'),
                        'entry_type'         => 'auto_adjustment'
                    ]);
                } else {
                    $finalAmount = $monthlyRent;
                }
            } else {
                // If not rent_received, we don't apply adjustment
                $finalAmount = $request->input('amount_bdt');
                $party->update([
                    'remaining_security_money' => $party->remaining_security_money - $finalAmount,
                    'refund_security_money'    => $party->security_money,
                ]);
            }

            // Always create main posting
            $cashReceivedPosting = RentalPosting::create([
                'transaction_type'   => $mapping[$transactionType],
                'head_id'            => $request->input('head_id'),
                'house_id'           => $request->input('house_id'),
                'payment_channel_id' => $request->input('payment_channel_id'),
                'account_id'         => $request->input('account_id'),
                'receipt_number'     => $request->input('receipt_number'),
                'amount_bdt'         => $monthlyRent,
                'posting_date'       => $request->input('posting_date'),
                'rent_received'      => $request->input('rent_received'),
                'note'               => $request->input('note'),
                'entry_type'         => $transactionType
            ]);

            if (!empty($request->input('other_cost_bdt')) && $request->input('other_cost_bdt') > 0) {
                RentalPosting::create([
                    'transaction_type'   => $mapping['other_amount'],
                    'head_id'            => $request->input('head_id'),
                    'house_id'           => $request->input('house_id'),
                    'payment_channel_id' => $request->input('payment_channel_id'),
                    'account_id'         => $request->input('account_id'),
                    'receipt_number'     => $request->input('receipt_number'),
                    'amount_bdt'         => $request->input('other_cost_bdt'),
                    'posting_date'       => $request->input('posting_date'),
                    'rent_received'      => $request->input('rent_received'),
                    'note'               => $request->input('note'),
                    'entry_type'         => 'other_amount'
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Created successfully.',
                'data'    => $cashReceivedPosting,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
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
        $source = RentalPosting::find($id);

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
        $posting = RentalPosting::find($id);

        if (!$posting) {
            return response()->json([
                'status' => false,
                'message' => 'Posting not found.'
            ], 404);
        }

        if ($posting->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot update a posting that is not in pending status.',
            ], 403);
        }

        $mapping = [
            'rent_received' => 'received',
            'security_money_refund' => 'payment',
        ];

        $updateData = $request->only([
            'head_id',
            'payment_channel_id',
            'account_id',
            'receipt_number',
            'amount_bdt',
            'other_cost_bdt',
            'posting_date',
            'note',
            'remaining_security_money_bdt'
        ]);

        if ($request->has('transaction_type')) {
            $transactionTypeInput = $request->input('transaction_type');

            if (!isset($mapping[$transactionTypeInput])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid transaction type provided for update.',
                ], 422);
            }

            $updateData['transaction_type'] = $mapping[$transactionTypeInput];
            $updateData['entry_type'] = $transactionTypeInput;
        }

        try {
            DB::beginTransaction();

            // Store old values for calculation
            $oldAmount = (float) $posting->amount_bdt;
            $oldTransactionType = $posting->transaction_type;
            $accountId = $posting->account_id;

            // Update the RentalPosting
            $posting->update($updateData);

            // Get new values
            $newAmount = (float) $request->input('amount_bdt', $posting->amount_bdt);
            $newTransactionType = $request->input('transaction_type') ? $mapping[$request->input('transaction_type')] : $posting->transaction_type;

            // Update Account Current Balance
            // $currentBalance = AccountCurrentBalance::where('account_id', $accountId)->first();

            // if (!$currentBalance) {
            //     throw new \Exception("No current balance record found for account ID: $accountId");
            // }

            // // Calculate the difference and update balance
            // $amountDifference = $newAmount - $oldAmount;
            // $transactionTypeChanged = ($oldTransactionType !== $newTransactionType);

            // if ($transactionTypeChanged) {
            //     // If transaction type changed, reverse old transaction and apply new one
            //     if ($oldTransactionType === 'received') {
            //         $currentBalance->balance -= $oldAmount; // Reverse old received
            //     } elseif ($oldTransactionType === 'payment') {
            //         $currentBalance->balance += $oldAmount; // Reverse old payment
            //     }

            //     // Apply new transaction
            //     if ($newTransactionType === 'received') {
            //         $currentBalance->balance += $newAmount;
            //     } elseif ($newTransactionType === 'payment') {
            //         $currentBalance->balance -= $newAmount;
            //     }
            // } else {
            //     // If same transaction type, just adjust the difference
            //     if ($newTransactionType === 'received') {
            //         $currentBalance->balance += $amountDifference;
            //     } elseif ($newTransactionType === 'payment') {
            //         $currentBalance->balance -= $amountDifference;
            //     }
            // }

            // $currentBalance->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Updated successfully.',
                'data' => $posting->fresh(),
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
        $source = RentalPosting::find($id);

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
        $posting = RentalPosting::find($id);

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
        $posting = RentalPosting::find($id);

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
        $posting = RentalPosting::find($id);

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
