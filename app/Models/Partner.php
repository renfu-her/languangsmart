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
}

