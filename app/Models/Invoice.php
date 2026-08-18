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

                $closingDate = $this->due_date
                    ->copy()
                    ->setDay((int) $this->creditCard->closing_day);

                if ($closingDate->greaterThan($this->due_date)) {
                    $closingDate->subMonthNoOverflow();
                }

                return $closingDate;
            }
        );
    }

    /**
     * Data de fechamento do ciclo de fatura ao qual a transação pertence.
     *
     * O ciclo é determinado pelo dia de fechamento do cartão, não pelo mês
     * civil da transação: compras até (e incluindo) o dia de fechamento
     * pertencem ao ciclo que fecha nesse mês; compras depois do fechamento
     * pertencem ao ciclo que fecha no mês seguinte.
     */
    public static function closingDateForTransaction(Carbon $transactionDate, CreditCard $creditCard): Carbon
    {
        $closingDay = (int) $creditCard->closing_day;

        $closingThisCycle = $transactionDate->copy()->day(min($closingDay, $transactionDate->daysInMonth));

        if ($transactionDate->lessThanOrEqualTo($closingThisCycle)) {
            return $closingThisCycle;
        }

        $nextMonth = $transactionDate->copy()->addMonthNoOverflow();

        return $nextMonth->day(min($closingDay, $nextMonth->daysInMonth));
    }

    /**
     * Data de vencimento correspondente a uma data de fechamento de ciclo.
     */
    public static function dueDateForClosing(Carbon $closingDate, CreditCard $creditCard): Carbon
    {
        $dueDay = (int) $creditCard->due_day;

        $dueDate = $closingDate->copy()->day(min($dueDay, $closingDate->daysInMonth));

        if ($dueDate->lessThan($closingDate)) {
            $dueDate = $dueDate->addMonthNoOverflow();
            $dueDate = $dueDate->day(min($dueDay, $dueDate->daysInMonth));
        }

        return $dueDate;
    }

    public static function invoiceByTransaction(Transaction $transaction): Invoice
    {
        $creditCard = $transaction->creditCard()->first();

        $closingDate = static::closingDateForTransaction($transaction->transaction_date, $creditCard);

        $invoice = $creditCard->invoices()
            ->whereMonth('reference_month', $closingDate->month)
            ->whereYear('reference_month', $closingDate->year)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        $dueDate = static::dueDateForClosing($closingDate, $creditCard);

        return Invoice::create([
            'reference_month' => $closingDate,
            'due_date' => $dueDate,
            'is_closed' => 0,
            'is_paid' => 0,
            'credit_card_id' => $creditCard->id,
        ]);
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
