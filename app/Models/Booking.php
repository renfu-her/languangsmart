<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'line_id',
        'phone',
        'scooter_type',
        'booking_date',
        'rental_days',
        'note',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];
}
