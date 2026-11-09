<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TransactionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'wash_transaction_id',
        'photo_type',
        'photo_path',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WashTransaction::class, 'wash_transaction_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
