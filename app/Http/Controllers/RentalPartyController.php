<?php

namespace App\Http\Controllers;

use App\Models\RentalHousePartyMap;
use App\Models\RentalMapping;
use Illuminate\Http\Request;
use App\Models\RentalParty;
use Illuminate\Support\Facades\DB;

class RentalPartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getPartyInfo(Request $request)
    {
      try {
          $selectedMonth = $request->rent_received;

          $party = RentalMapping::where('party_id', $request->party_id)
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

          if (!$party) {
              return response()->json([
                  'status' => false,
                  'message' => 'No party mapping found for this house.',
                  'data' => null,
              ], 500);
          }

            return response()->json([
                'status'  => true,
                'message' => 'Retrive successfully.',
                'data'    => $party,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Transaction failed. ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function getPartyRefundInfo($id)
    // {
    //     try {
    //         $party = DB::table('rental_postings as rp')
    //             ->join('rental_mappings as rpy', 'rpy.house_id', '=', 'rp.house_id')
    //             ->select(
    //                 'rp.house_id',
    //                 'rpy.security_money',
    //                 DB::raw('SUM(CASE WHEN rp.entry_type = "auto_adjustment" THEN rp.amount_bdt ELSE 0 END) as total_adjustment'),
    //                 DB::raw('(rpy.security_money 
    //                     - SUM(
    //                         CASE 
    //                             WHEN rp.entry_type IN ("auto_adjustment","security_money_refund") 
    //                             THEN rp.amount_bdt 
    //                             ELSE 0 
    //                         END
    //                     )
    //                 ) AS total_payable')
    //             )
    //             ->where('rp.house_id', $id)
    //             ->groupBy('rp.house_id', 'rpy.security_money')
    //             ->first();

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Retrieved successfully.',
    //             'data'    => $party,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Transaction failed. ' . $e->getMessage(),
    //             'data'    => null
    //         ], 500);
    //     }
    // }

 public function getPartyRefundInfo($id)
    {
        try {
            $party = DB::table('rental_postings as rp')
    ->join('rental_mappings as rpy', 'rpy.house_id', '=', 'rp.house_id')
    ->select(
        'rp.house_id',
        // Replace rpy.security_money with sum of approved security_money_amount
        DB::raw('SUM(CASE WHEN rp.entry_type = "security_money_amount" AND rp.status = "approved" THEN rp.amount_bdt ELSE 0 END) as security_money'),

        DB::raw('SUM(CASE WHEN rp.entry_type = "auto_adjustment" THEN rp.amount_bdt ELSE 0 END) as total_adjustment'),

        DB::raw('(SUM(CASE WHEN rp.entry_type = "security_money_amount" AND rp.status = "approved" THEN rp.amount_bdt ELSE 0 END)
            - SUM(
                CASE 
                    WHEN rp.entry_type IN ("auto_adjustment","security_money_refund") 
                    THEN rp.amount_bdt 
                    ELSE 0 
                END
            )
        ) AS total_payable')
    )
    ->where('rp.house_id', $id)
    ->groupBy('rp.house_id')
    ->first();



            return response()->json([
                'status'  => true,
                'message' => 'Retrieved successfully.',
                'data'    => $party,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction failed. ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function getAllParties(Request $request)
    {
        $source = RentalParty::all();

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

            ->select(
                'rp.*',
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

        if (RentalParty::where('party_name', $request->input('party_name'))->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'A rental party with this name already exists.',
                'data' => null
            ], 409);
        }


        try {
            $data = $request->all();
            $source = RentalParty::create($data);

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
        $source = RentalParty::find($id);

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
    public function update(Request $request, $id)
    {

        $party = RentalParty::find($id);

        if (!$party) {
            return response()->json([
                'status' => false,
                'message' => 'Rental Party not found.',
                'data' => null
            ], 404);
        }

        if (RentalParty::where('party_name', $request->input('party_name'))
            ->where('id', '!=', $id)
            ->exists()
        ) {
            return response()->json([
                'status' => false,
                'message' => 'A rental party with this name already exists.',
                'data' => null
            ], 409);
        }


        try {

            $data = $request->all();
            $party->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Rental Party and associated houses updated successfully.',
                'data' => $party
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
        $source = RentalParty::find($id);

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


    // In your controller
    // public function getHouseMappingsByParty($partyId)
    // {
    //     try {
    //         $houseMappings = RentalHousePartyMap::with('rentalHouse')
    //             ->where('rental_party_id', $partyId)
    //             ->where('status', 'active')
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'House mappings retrieved successfully.',
    //             'data' => $houseMappings
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to retrieve house mappings.'
    //         ], 500);
    //     }
    // }


    public function getHouseMappingsByParty($partyId)
    {
        try {
            // Validate if party exists
            $party = RentalParty::find($partyId);

            if (!$party) {
                return response()->json([
                    'status' => false,
                    'message' => 'Party not found.'
                ], 404);
            }

            // Raw SQL query to get house mappings
            $houseMappings = DB::select("
            SELECT rh.id, rh.house_name 
            FROM rental_houses rh
            JOIN rental_house_party_maps rhpm ON rh.id = rhpm.rental_house_id
            WHERE rhpm.rental_party_id = ? 
            AND rhpm.status = 'active'
        ", [$partyId]);
            return response()->json([
                'status' => false,
                'data' => $houseMappings,
                'message' => 'House mappings retrieved successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve house mappings.',
                'error' => $e->getMessage()
            ], 500);
        }

        // If no mappings found, return empty array
        //     if (empty($houseMappings)) {
        //         return response()->json([
        //             'status' => true,
        //             'message' => 'No house mappings found for this party.',
        //             'data' => []
        //         ], 200);
        //     }

        //     return response()->json([
        //         'status' => true,
        //         'message' => 'House mappings retrieved successfully.',
        //         'data' => $houseMappings
        //     ], 200);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Failed to retrieve house mappings.',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }
}
