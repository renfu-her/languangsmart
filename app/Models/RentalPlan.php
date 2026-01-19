<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'price',
        'image_path',
        'sort_order',
        'is_active',
        'store_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
