<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasherCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'commission_type',
        'commission_value',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
    ];

    public function washer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
