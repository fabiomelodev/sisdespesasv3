<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImmediateExpense extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'pay_day' => 'datetime'
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
