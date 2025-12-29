<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'partner_id',
        'tenant',
        'appointment_date',
        'start_time',
        'end_time',
        'expected_return_time',
        'phone',
        'shipping_company',
        'ship_arrival_time',
        'ship_return_time',
        'payment_method',
        'payment_amount',
        'status',
        'remark',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'expected_return_time' => 'datetime',
        'ship_arrival_time' => 'datetime',
        'ship_return_time' => 'datetime',
        'payment_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Get the partner that owns the order.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the scooters for the order.
     */
    public function scooters()
    {
        return $this->belongsToMany(Scooter::class, 'order_scooter');
    }

    /**
     * Get the fines for the order.
     */
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }
}

