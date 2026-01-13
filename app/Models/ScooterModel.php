<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScooterModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'image_path',
        'color',
    ];

    /**
     * Get the scooters for this model.
     */
    public function scooters()
    {
        return $this->hasMany(Scooter::class);
    }

    /**
     * Get the transfer fees for this model.
     */
    public function transferFees()
    {
        return $this->hasMany(PartnerScooterModelTransferFee::class);
    }
}
