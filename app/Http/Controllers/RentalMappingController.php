<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RentalHousePartyMap;
use App\Models\RentalParty;
use App\Models\RentalPosting;
use App\Models\RentalMapping;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentalMappingController extends Controller
{
  
  public function getPartyWiseHouses($id)
    {
        $source = DB::select("SELECT 
        rh.id,
        rh.house_name
    FROM rental_house_party_maps AS rhpm
    INNER JOIN rental_houses AS rh 
        ON rh.id = rhpm.rental_house_id
    WHERE rhpm.rental_party_id = '$id'
    AND rhpm.status = 'active'
     ");

        return response()->json([
            'status' => true,
            'message' => 'Retrieved successfully.',
            'data' => $source,

        ], 200);
    }
    public function getAllMappings(Request $request)
    {
        $source = DB::table('rental_house_party_maps as rhpm')
            ->join('rental_houses as rh', 'rh.id', '=', 'rhpm.rental_house_id')
            ->select('rhpm.*', 'rh.house_name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Retrieved successfully.',
            'data' => $source,

        ], 200);
    }

    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);

        $source = DB::table('rental_parties as rp')
            ->join('rental_mappings as rhpm', 'rhpm.party_id', '=', 'rp.id')
            ->join('rental_houses as rh', 'rh.id', '=', 'rhpm.house_id')
            ->join('payment_channel_details as pcd', 'pcd.id', '=', 'rhpm.payment_channel_id')
            ->join('account_numbers as ac', 'pcd.id', '=', 'ac.channel_detail_id')
            ->select(
                'rp.id as party_id',
                'rp.party_name',
                'rp.cell_number',
                'rp.nid',
                'rp.party_ac_no',
                'rhpm.security_money',
                'rhpm.remaining_security_money',
                'rhpm.id',
                'rhpm.house_id',
                'rhpm.auto_adjustment',
                'rhpm.monthly_rent',
                'rhpm.refund_security_money',
                'rhpm.remaining_security_money',
                'rhpm.rent_start_date',
                'rhpm.rent_end_date',
                'rhpm.status',
                'rp.party_name',
                'rp.cell_number',
                'rh.id as house_id',
                'rh.house_name',
                'rh.address',
                'pcd.method_name',
                'rhpm.payment_channel_id',
                'rhpm.account_id',
                'ac.ac_no',
                'ac.ac_name'
            )
            ->paginate($pageSize);

        return response()->json([
            'status' => true,
            'message' => 'Retrieved successfully.',
            'data' => $source->items(),
            'total' => $source->total(),
            'current_page' => $source->currentPage(),
            'last_page' => $source->lastPage(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $partyId = $request->input('party_name');
        $houseId = $request->input('house');
        $startDate = $request->input('rent_start_date');
        $endDate = $request->input('rent_end_date');
        $prevEndMonth = Carbon::parse($startDate . '-01')
                        ->subMonth()
                        ->format('Y-m');


        try {
            DB::beginTransaction();

            $existing = RentalMapping::where('party_id', $partyId)
                ->where('house_id', $houseId)
                ->where('status', 'active')
                ->where('rent_start_date', $startDate)
                ->first();



            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'A Rent Start Date already exists for this party and this house.',
                    'data' => null
                ], 409);

            }




            $data = [
                'party_id'   => $request->input('party_name'),
                'house_id'   => $request->input('house'),
                'security_money'    => !empty($request->input('security_money'))
                    ? $request->input('security_money')
                    : 0.00,
                'remaining_security_money' => !empty($request->input('security_money'))
                    ? $request->input('security_money')
                    : 0.00,
                'monthly_rent'      => $request->input('monthly_rent'),
                'auto_adjustment'   => !empty($request->input('auto_adjustment'))
                    ? $request->input('auto_adjustment')
                    : 0.00,
                'payment_channel_id' => $request->input('payment_channel_id'),
                'account_id'         => $request->input('account_id'),
                'rent_start_date'    => $request->input('rent_start_date'),
                'rent_end_date'      => $request->input('rent_end_date'),
                'status'             => $request->input('status'),
            ];

            // $latestRentalMapping = RentalMapping::where('party_id', $partyId)
            // ->where('house_id', $houseId)
            // ->where('status', 'active')
            // ->latest('id')
            // ->first();

            // if ($latestRentalMapping) {
            //     $latestRentalMapping->rent_end_date = $prevEndMonth;
            //     $latestRentalMapping->status = 'inactive';
            //     $latestRentalMapping->save();
            // }

            $source = RentalMapping::create($data);


            // if (!empty($request->input('security_money')) && $request->input('security_money') > 0) {
            //     $latestSecurityMoney = RentalPosting::where('head_id', $request->input('party_name'))
            //         ->where('house_id', $request->input('house'))
            //         ->where('entry_type', 'security_money_amount')->latest('id')
            //  ->first();


            //     if ($latestSecurityMoney->amount_bdt < $request->input('security_money')) {

            // RentalPosting::create([
            //     'transaction_type'   => 'received',
            //     'head_id'            => $request->input('party_name'),
            //     'house_id'           => $request->input('house'),
            //     'payment_channel_id' => $request->input('payment_channel_id'),
            //     'account_id'         => $request->input('account_id'),
            //     'receipt_number'     => $request->input('receipt_number', null),
            //     'amount_bdt'         => $request->input('security_money'),
            //     'posting_date'       => Carbon::now()->toDateString(),
            //     'rent_received'       => null,
            //     'note'               => $request->input('note', null),
            //     'entry_type'         => 'security_money_amount',
            //     'status'             => 'approved',
            // ]);

            //     }
            // }


            if (!empty($request->input('security_money')) && $request->input('security_money') > 0) {

                $inputSecurity = $request->input('security_money');

                $latestSecurityMoney = RentalPosting::where('head_id', $request->input('party_name'))
                    ->where('house_id', $request->input('house'))
                    ->where('entry_type', 'security_money_amount')
                    ->latest('id')
                    ->first();

                // If no previous record exists → create new
                if (!$latestSecurityMoney || $inputSecurity > $latestSecurityMoney->amount_bdt) {

                    RentalPosting::create([
                        'transaction_type'   => 'received',
                        'head_id'            => $request->input('party_name'),
                        'house_id'           => $request->input('house'),
                        'payment_channel_id' => $request->input('payment_channel_id'),
                        'account_id'         => $request->input('account_id'),
                        'receipt_number'     => $request->input('receipt_number', null),
                        'amount_bdt'         => $inputSecurity,
                        'posting_date'       => Carbon::now()->toDateString(),
                        'rent_received'      => null,
                        'note'               => $request->input('note', null),
                        'entry_type'         => 'security_money_amount',
                        'status'             => 'approved',
                    ]);
                }
            }


            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Rental Party and associated houses created successfully.',
                'data' => $source
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the source by ID
        $source = RentalHousePartyMap::find($id);

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

    // public function show(string $id)
    // {
    //     // Find all house mappings for this party ID
    //     $houseMappings = RentalHousePartyMap::with('rentalHouse')
    //         ->where('rental_party_id', $id)
    //         ->where('status', 'active')
    //         ->get();

    //     // If no mappings found, return error response
    //     if ($houseMappings->isEmpty()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No house mappings found for this party.'
    //         ], 404);
    //     }

    //     // Return the house mappings as a JSON response
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'House mappings retrieved successfully.',
    //         'data' => $houseMappings
    //     ], 200);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
      
        $partyId = $request->input('party_name');
        $houseId = $request->input('house');

        $rentMaps = RentalMapping::find($id);

        if ($rentMaps->remaining_security_money > 0){
            return response()->json([
                'status' => false,
                'message' => 'Please refund security money',
                'data' => null
            ], 500);
        
        }
        $rentSecurityMoneyPosting = RentalPosting::where([
            'head_id'   => $partyId,
            'house_id'  => $houseId,
            'entry_type' => 'security_money_amount'
        ])->first();


        if (!$rentMaps) {
            return response()->json([
                'status' => false,
                'message' => 'Rental Party not found.',
                'data' => null
            ], 500);
        }


        try {
            DB::beginTransaction();
            $data = [
                    'party_id'   => $partyId,
                    'house_id'   => $houseId,
                    'security_money'    => !empty($request->input('security_money'))
                        ? $request->input('security_money')
                        : 0.00,
                    'remaining_security_money' => !empty($request->input('security_money'))
                        ? $request->input('security_money')
                        : 0.00,
                    'monthly_rent'      => $request->input('monthly_rent'),
                    'auto_adjustment'   => !empty($request->input('auto_adjustment'))
                        ? $request->input('auto_adjustment')
                        : 0.00,
                    'payment_channel_id' => $request->input('payment_channel_id'),
                    'account_id'        => $request->input('account_id'),
                    'rent_start_date'   => $request->input('rent_start_date'),
                    'rent_end_date'     => $request->input('rent_end_date'),
                    'status'            => $request->input('status'),
                ];


            $rentMaps->update($data);
            $rentSecurityMoneyPosting?->update(['amount_bdt' => $request->security_money]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Rental Party and associated houses updated successfully.',
                'data' => $rentMaps
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update the rental party due to a database error. ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $source = RentalHousePartyMap::find($id);

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


    // public function getHouseMappingsByParty($partyId)
    // {
    //     try {
    //         // Validate if party exists
    //         $party = RentalParty::find($partyId);

    //         if (!$party) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Party not found.'
    //             ], 404);
    //         }

    //         // Raw SQL query to get house mappings
    //         $houseMappings = DB::select("
    //         SELECT rh.id, rh.house_name
    //         FROM rental_houses rh
    //         JOIN rental_house_party_maps rhpm ON rh.id = rhpm.rental_house_id
    //         WHERE rhpm.rental_party_id = ?
    //         AND rhpm.status = 'active'
    //     ", [$partyId]);


    //         return response()->json([$houseMappings], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to retrieve house mappings.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }

    //     // If no mappings found, return empty array
    //     //     if (empty($houseMappings)) {
    //     //         return response()->json([
    //     //             'status' => true,
    //     //             'message' => 'No house mappings found for this party.',
    //     //             'data' => []
    //     //         ], 200);
    //     //     }

    //     //     return response()->json([
    //     //         'status' => true,
    //     //         'message' => 'House mappings retrieved successfully.',
    //     //         'data' => $houseMappings
    //     //     ], 200);
    //     // } catch (\Exception $e) {
    //     //     return response()->json([
    //     //         'status' => false,
    //     //         'message' => 'Failed to retrieve house mappings.',
    //     //         'error' => $e->getMessage()
    //     //     ], 500);
    //     // }
    // }
}
