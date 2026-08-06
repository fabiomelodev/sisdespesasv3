<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'datetime',
        'is_paid' => 'boolean'
    ];

    public const EXPENSE = 'expense';

    public const INCOME = 'income';

    public const GOAL = 'goal';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->payment_method == 'credit' && $model->invoice_id == null) {
                $invoice = Invoice::invoiceByTransaction($model);

                $model->invoice_id = $invoice->id;
            }
        });

        static::updated(function ($model) {
            if ($model->is_paid) {
                if ($model->transactionGroup()->exists()) {
                    $transactionGroup = $model->transactionGroup;

                    $transaction = $transactionGroup->transactions()->orderBy('transaction_date', 'desc')->first();

                    if ($transaction->is_paid) {
                        $transactionGroup->is_paid = 1;

                        $transactionGroup->save();
                    }
                }
            }
        });
    }

    public function scopeIsExpense(Builder $query): Builder
    {
        return $query->where('type', static::EXPENSE);
    }

    public function scopeIsIncome(Builder $query): Builder
    {
        return $query->where('type', static::INCOME);
    }

    public function scopeIsPaid(Builder $query): Builder
    {
        return $query->where('is_paid', 1);
    }

    public function scopeNotIsPaid(Builder $query): Builder
    {
        return $query->where('is_paid', 0);
    }

    public function scopeMonthCurrent(Builder $query): Builder
    {
        return $query->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year);
    }

    public function scopeIsGoal(Builder $query): Builder
    {
        return $query->where('type', static::GOAL);
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function transactionGroup(): BelongsTo
    {
        return $this->belongsTo(TransactionGroup::class);
    }
}
