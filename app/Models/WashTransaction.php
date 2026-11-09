<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'washer_id',
        'vehicle_type_id',
        'washing_service_id',
        'price',
        'commission_amount',
        'business_revenue',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'business_revenue' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function washer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'washer_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function washingService(): BelongsTo
    {
        return $this->belongsTo(WashingService::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TransactionPhoto::class);
    }

    public function photoBefore(): HasOne
    {
        return $this->hasOne(TransactionPhoto::class)->where('photo_type', 'before');
    }

    public function photoAfter(): HasOne
    {
        return $this->hasOne(TransactionPhoto::class)->where('photo_type', 'after');
    }
}
