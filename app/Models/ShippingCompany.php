<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store_id',
        'color',
    ];

    /**
     * Get the store that owns the shipping company.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
