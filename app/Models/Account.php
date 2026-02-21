<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'status' => 'boolean'
    ];

    protected static function booted()
    {
        static::creating(function ($account) {
            $account->balance = $account->initial_balance;
        });
    }
}
