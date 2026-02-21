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

    public const TRANSFER = 'transfer';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->payment_method == 'credit') {
                $invoice = Invoice::invoiceByTransaction($model);

                $model->invoice_id = $invoice->id;
            }
        });

        static::created(function ($transaction) {
            $transaction->adjustBalances(true);
        });

        static::deleted(function ($transaction) {
            $transaction->adjustBalances(false); // Reverte o cálculo ao deletar
        });
    }

    public function adjustBalances(bool $isIncrementing)
    {
        $factor = $isIncrementing ? 1 : -1;

        if ($this->destination_account_id) {
            if ($this->type === static::EXPENSE) {
                $this->account->decrement('balance', $this->amount * $factor);
            } elseif ($this->type === static::INCOME) {
                $this->account->increment('balance', $this->amount * $factor);
            } elseif ($this->type === static::TRANSFER) {
                $this->account->decrement('balance', $this->amount * $factor);
                $this->destinationAccount->increment('balance', $this->amount * $factor);
            }
        }

        if ($this->goal_id) {
            if ($this->type === static::EXPENSE) {
                $this->goal->decrement('balance', $this->amount * $factor);
            } elseif ($this->type === static::INCOME) {
                $this->goal->increment('balance', $this->amount * $factor);
            } elseif ($this->type === static::TRANSFER || $this->type === static::GOAL) {
                $this->goal->increment('balance', $this->amount * $factor);
            }
        }
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

    public function scopeMonthCurrent(Builder $query): Builder
    {
        return $query->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year);
    }

    public function scopeIsTransfer(Builder $query): Builder
    {
        return $query->where('type', static::TRANSFER);
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

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
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
