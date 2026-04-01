<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionGroup extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'purchase_date' => 'datetime',
        'is_paid' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $transactionAmount = $model->total_amount / $model->installments;

            for ($i = 1; $i <= $model->installments; $i++) {
                Transaction::create([
                    'name' => $model->name . ' ' . $i . '/' . $model->installments,
                    'type' => 'expense',
                    'amount' => $transactionAmount,
                    'payment_method' => $model->payment_method,
                    'installment_number' => $i,
                    'transaction_date' => $model->purchase_date->copy()->addMonthsNoOverflow($i),
                    'is_paid' => false,
                    'account_id' => $model->account_id,
                    'category_id' => $model->category_id,
                    'credit_card_id' => $model?->credit_card_id,
                    'transaction_group_id' => $model->id,
                ]);
            }
        });

        static::deleted(function ($model) {
            $model->transactions()->each(function ($transaction) {
                $transaction->delete();
            });
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
