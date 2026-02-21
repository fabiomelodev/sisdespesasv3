<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['entry_date' => 'datetime'];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
