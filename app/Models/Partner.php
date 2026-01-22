<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'tax_id',
        'manager',
        'photo_path',
        'color',
        'is_default_for_booking',
        'default_shipping_company',
        'same_day_transfer_fee_white',
        'same_day_transfer_fee_green',
        'same_day_transfer_fee_electric',
        'same_day_transfer_fee_tricycle',
        'overnight_transfer_fee_white',
        'overnight_transfer_fee_green',
        'overnight_transfer_fee_electric',
        'overnight_transfer_fee_tricycle',
        'store_id',
    ];

    /**
     * Get the scooters for the partner.
     */
    public function scooters()
    {
        return $this->hasMany(Scooter::class);
    }

    /**
     * Get the orders for the partner.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the scooter model transfer fees for the partner.
     */
    public function scooterModelTransferFees()
    {
        return $this->hasMany(PartnerScooterModelTransferFee::class);
    }

    /**
     * Get the store that owns the partner.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

