<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeIsProgress(Builder $query): Builder
    {
        return $query->where('status', 'progress');
    }

    public function scopeIsCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
