<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Invoice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'reference_month' => 'datetime',
        'due_date' => 'datetime',
        'is_closed' => 'boolean',
        'is_paid' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->is_paid) {
                $model->transactions()->each(function ($transaction) {
                    $transaction->is_paid = 1;

                    $transaction->save();
                });
            } else {
                $model->transactions()->each(function ($transaction) {
                    $transaction->is_paid = 0;

                    $transaction->save();
                });
            }
        });
    }

    public function scopeIsPaid(Builder $query): Builder
    {
        return $query->where('is_paid', 1);
    }

    public function scopeIsPeding(Builder $query): Builder
    {
        return $query->where('is_paid', 0);
    }

    public function scopeMonthCurrent(Builder $query): Builder
    {
        return $query->whereMonth('due_date', now()->month)->whereYear('due_date', now()->year);
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->transactions()->sum('amount'),
        );
    }

    protected function closingDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->due_date || !$this->creditCard) {
                    return null;
                }

                return $this->due_date
                    ->copy()
                    ->setDay((int) $this->creditCard->closing_day);
            }
        );
    }

    public static function hasInvoiceByTransaction(Transaction $transaction): bool
    {
        return $transaction->creditCard()->first()->invoices()->where(function (Builder $query) use ($transaction) {
            $query->whereMonth('reference_month', $transaction->transaction_date)->whereYear('reference_month', $transaction->transaction_date);
        })->exists();
    }

    public static function createInvoiceByTransaction(Transaction $transaction): Invoice
    {
        $nextMonth = $transaction->transaction_date
            ->copy()
            ->addMonthNoOverflow();

        $dueDate = $nextMonth->setDay((int) $transaction->creditCard->due_day);

        return Invoice::create([
            'reference_month' => $transaction->transaction_date,
            'due_date' => $dueDate,
            'is_closed' => 0,
            'is_paid' => 0,
            'credit_card_id' => $transaction->creditCard()->first()->id
        ]);
    }

    public static function invoiceByTransaction(Transaction $transaction): Invoice
    {
        if (static::hasInvoiceByTransaction($transaction)) {
            return $transaction->creditCard()->first()->invoices()->where(function (Builder $query) use ($transaction) {
                $query->whereMonth('reference_month', $transaction->transaction_date)->whereYear('reference_month', $transaction->transaction_date);
            })->first();
        }

        return static::createInvoiceByTransaction($transaction);
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
