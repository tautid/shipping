<?php

namespace TautId\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingActivity extends Model
{
    protected $table = 'taut_shipping_activities';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class,'shipping_id');
    }
}
