<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCard extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function hasHistory(): bool
    {
        return $this->invoices()->exists() || $this->transactions()->exists();
    }

    /**
     * Limite usado: despesas já ocorridas até o fim do mês atual em faturas
     * ainda não pagas. Filtra pela data da transação (não da fatura) para
     * não contar parcelas futuras ainda não cobradas, mesmo que já exista
     * uma fatura criada à frente para elas.
     */
    public function usedLimit(): float
    {
        return (float) $this->invoices()
            ->where('invoices.is_paid', false)
            ->join('transactions', 'transactions.invoice_id', '=', 'invoices.id')
            ->where('transactions.type', 'expense')
            ->where('transactions.transaction_date', '<=', Carbon::now()->endOfMonth())
            ->sum('transactions.amount');
    }
}
