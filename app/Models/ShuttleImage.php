<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShuttleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'sort_order',
        'store_id',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'store_id' => 'integer',
    ];

    /**
     * Get the store that owns the shuttle image.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
