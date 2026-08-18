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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->payment_method == 'credit') {
                if ($model->invoice_id == null || $model->isDirty('transaction_date') || $model->isDirty('credit_card_id')) {
                    $invoice = Invoice::invoiceByTransaction($model);

                    $model->invoice_id = $invoice->id;
                }
            } elseif ($model->invoice_id !== null) {
                $model->invoice_id = null;
            }
        });

        static::updated(function ($model) {
            if ($model->is_paid) {
                if ($model->transactionGroup()->exists()) {
                    $transactionGroup = $model->transactionGroup;

                    if (!$transactionGroup->is_paid && $transactionGroup->transactions()->notIsPaid()->doesntExist()) {
                        $transactionGroup->is_paid = 1;

                        $transactionGroup->save();
                    }
                }

                if ($model->invoice_id !== null) {
                    $invoice = $model->invoice;

                    if ($invoice && !$invoice->is_paid && $invoice->transactions()->notIsPaid()->doesntExist()) {
                        $invoice->is_paid = 1;

                        $invoice->save();
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    public function transactionGroup(): BelongsTo
    {
        return $this->belongsTo(TransactionGroup::class);
    }
}
