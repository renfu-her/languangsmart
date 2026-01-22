<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guesthouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'image_path',
        'images',
        'link',
        'sort_order',
        'is_active',
        'store_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'images' => 'array',
        'store_id' => 'integer',
    ];

    /**
     * Get the store that owns the guesthouse.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
