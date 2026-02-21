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

    public const RESERVE = 'reserve';

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
        // Se isIncrementing é false, multiplicamos por -1 para fazer o inverso
        $factor = $isIncrementing ? 1 : -1;

        if ($this->type === 'expense') {
            $this->account->decrement('balance', $this->amount * $factor);
        } elseif ($this->type === 'revenue') {
            $this->account->increment('balance', $this->amount * $factor);
        } elseif ($this->type === 'transfer') {
            // Sai da origem (-), entra no destino (+)
            $this->account->decrement('balance', $this->amount * $factor);
            $this->destinationAccount->increment('balance', $this->amount * $factor);
        }
    }

    public function scopeTypeExpense(Builder $query): Builder
    {
        return $query->where('type', static::EXPENSE);
    }

    public function scopeTypeIncome(Builder $query): Builder
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

    public function scopeTypeTransfer(Builder $query): Builder
    {
        return $query->where('type', static::TRANSFER);
    }

    public function scopeTypeReserve(Builder $query): Builder
    {
        return $query->where('type', static::RESERVE);
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
