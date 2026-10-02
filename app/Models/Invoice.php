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

                if ($this->creditCard->closing_offset_days !== null) {
                    return $this->due_date->copy()->subDays((int) $this->creditCard->closing_offset_days);
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
     * Data de abertura do ciclo que fecha em $closingDate.
     *
     * Se o dia de abertura for depois do dia de fechamento (ex: abre dia 08,
     * fecha dia 07), o ciclo cruza a virada do mês e a abertura fica no mês
     * anterior ao fechamento. Caso contrário, abertura e fechamento ficam no
     * mesmo mês.
     */
    protected static function openingDateForClosing(Carbon $closingDate, CreditCard $creditCard): Carbon
    {
        $openingDay = (int) $creditCard->opening_day;
        $closingDay = (int) $creditCard->closing_day;

        $openingDate = $closingDate->copy();

        if ($openingDay > $closingDay) {
            $openingDate = $openingDate->subMonthNoOverflow();
        }

        return $openingDate->day(min($openingDay, $openingDate->daysInMonth));
    }

    /**
     * Data de fechamento do ciclo de fatura ao qual a transação pertence.
     *
     * O ciclo é determinado pelos dias de abertura e fechamento do cartão,
     * não pelo mês civil da transação: compras entre a abertura e o
     * fechamento (inclusive) pertencem a esse ciclo; compras depois do
     * fechamento pertencem ao próximo ciclo; compras antes da abertura
     * pertencem ao ciclo anterior.
     *
     * Se o cartão usa fechamento dinâmico (closing_offset_days), o ciclo é
     * calculado a partir do vencimento menos N dias corridos, em vez de um
     * dia fixo do mês — ver closingDateForTransactionByOffset().
     */
    public static function closingDateForTransaction(Carbon $transactionDate, CreditCard $creditCard): Carbon
    {
        if ($creditCard->closing_offset_days !== null) {
            return static::closingDateForTransactionByOffset($transactionDate, $creditCard);
        }

        $closingDay = (int) $creditCard->closing_day;

        $closingThisCycle = $transactionDate->copy()->day(min($closingDay, $transactionDate->daysInMonth));
        $openingThisCycle = static::openingDateForClosing($closingThisCycle, $creditCard);

        if ($transactionDate->between($openingThisCycle, $closingThisCycle)) {
            return $closingThisCycle;
        }

        if ($transactionDate->greaterThan($closingThisCycle)) {
            $nextMonth = $transactionDate->copy()->addMonthNoOverflow();

            return $nextMonth->day(min($closingDay, $nextMonth->daysInMonth));
        }

        $previousMonth = $transactionDate->copy()->subMonthNoOverflow();

        return $previousMonth->day(min($closingDay, $previousMonth->daysInMonth));
    }

    /**
     * Mesma lógica de closingDateForTransaction(), mas com o fechamento
     * calculado como "vencimento do ciclo menos N dias corridos" (regra do
     * NuBank), em vez de um dia fixo do mês. O vencimento continua sendo um
     * dia fixo do mês (due_day); só o fechamento se desloca com o tamanho
     * do mês anterior.
     */
    protected static function closingDateForTransactionByOffset(Carbon $transactionDate, CreditCard $creditCard): Carbon
    {
        $dueDay = (int) $creditCard->due_day;
        $offset = (int) $creditCard->closing_offset_days;

        // Os ciclos são contínuos e crescentes: o fechamento do ciclo da
        // transação é o primeiro fechamento (vencimento do mês candidato
        // menos N dias) que não fica antes da data da transação.
        $candidate = $transactionDate->copy()->subMonthsNoOverflow(2);

        for ($i = 0; $i < 48; $i++) {
            $closing = $candidate->copy()
                ->day(min($dueDay, $candidate->daysInMonth))
                ->subDays($offset);

            if ($closing->greaterThanOrEqualTo($transactionDate)) {
                return $closing;
            }

            $candidate = $candidate->addMonthNoOverflow();
        }

        throw new \RuntimeException('Não foi possível determinar o ciclo de fechamento dinâmico.');
    }

    /**
     * Data de vencimento correspondente a uma data de fechamento de ciclo.
     */
    public static function dueDateForClosing(Carbon $closingDate, CreditCard $creditCard): Carbon
    {
        if ($creditCard->closing_offset_days !== null) {
            return $closingDate->copy()->addDays((int) $creditCard->closing_offset_days);
        }

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
