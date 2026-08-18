<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
            $installments = (int) $model->installments;
            $baseAmount = round($model->total_amount / $installments, 2);
            $allocatedAmount = 0;

            for ($i = 1; $i <= $installments; $i++) {
                $isLastInstallment = $i === $installments;
                $amount = $isLastInstallment ? round($model->total_amount - $allocatedAmount, 2) : $baseAmount;
                $allocatedAmount += $amount;

                Transaction::create([
                    'name' => $model->name . ' ' . $i . '/' . $model->installments,
                    'type' => 'expense',
                    'amount' => $amount,
                    'payment_method' => $model->payment_method,
                    'installment_number' => $i,
                    'transaction_date' => $model->purchase_date->copy()->addMonthsNoOverflow($i - 1),
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

    public function scopeIsPaid(Builder $query): Builder
    {
        return $query->where('is_paid', 1);
    }

    public function scopeNotIsPaid(Builder $query): Builder
    {
        return $query->where('is_paid', 0);
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
