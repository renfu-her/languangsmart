<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerScooterModelTransferFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'scooter_model_id',
        'same_day_transfer_fee',
        'overnight_transfer_fee',
    ];

    /**
     * Get the partner that owns the transfer fee.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the scooter model for this transfer fee.
     */
    public function scooterModel()
    {
        return $this->belongsTo(ScooterModel::class);
    }
}
