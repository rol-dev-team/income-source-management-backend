<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalMapping extends Model
{
    protected $fillable = [
        'party_id',
        'house_id',
        'security_money',
        'remaining_security_money',
        'refund_security_money',
        'monthly_rent',
        'auto_adjustment',
        'payment_channel_id',
        'account_id',
        'rent_start_date',
        'rent_end_date',
        'status',
    ];


    public function rentalHouse()
    {
        return $this->belongsTo(RentalHouse::class, 'house_id');
    }

    public function rentalParty()
    {
        return $this->belongsTo(RentalParty::class, 'party_id');
    }

    public function rentalPostings()
    {
        return $this->hasMany(RentalPosting::class, 'head_id', 'party_id','rent_start_date','rent_end_date')
                    ->where('house_id', $this->house_id);
    }

    public function rentReceivedPostings()
    {
        return $this->rentalPostings()
                    ->where('entry_type', 'rent_received')
                    ->where('status', 'approved');
    }

    public function autoAdjustmentPostings()
    {
        return $this->rentalPostings()
                    ->where('entry_type', 'auto_adjustment')
                    ->where('status', 'approved');
    }
}
