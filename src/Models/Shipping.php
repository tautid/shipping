<?php

namespace TautId\Shipping\Models;

use TautId\Shipping\Transitions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use TautId\Shipping\Enums\ShippingStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TautId\Shipping\Traits\HasTransitionStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    use HasTransitionStatusTrait, SoftDeletes;

    public $stateConfigs = [
        ShippingStatusEnum::Draft->value => Transitions\ToDraft::class,
        ShippingStatusEnum::Requested->value => Transitions\ToRequested::class,
        ShippingStatusEnum::Delivering->value => Transitions\ToDelivering::class,
        ShippingStatusEnum::Delivered->value => Transitions\ToDelivered::class,
        ShippingStatusEnum::Canceled->value => Transitions\ToCanceled::class,
        ShippingStatusEnum::Failed->value => Transitions\ToFailed::class,
        ShippingStatusEnum::Returned->value => Transitions\ToReturned::class,
        ShippingStatusEnum::Lost->value => Transitions\ToLost::class
    ];

    protected $table = 'taut_shippings';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'delivered_at' => 'datetime',
        'last_check_status_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array',
        'dimension' => 'array',
        'payload' => 'array',
        'response' => 'array'
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class,'method_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ShippingActivity::class,'method_id');
    }
}
