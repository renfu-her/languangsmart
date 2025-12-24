<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'scooter_id',
        'order_id',
        'tenant',
        'violation_date',
        'violation_type',
        'fine_amount',
        'payment_status',
        'photo_path',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Get the scooter that owns the fine.
     */
    public function scooter()
    {
        return $this->belongsTo(Scooter::class);
    }

    /**
     * Get the order that owns the fine.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

