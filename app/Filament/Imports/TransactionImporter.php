<?php

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\LinkedTransaction;
use App\Models\Transaction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('amount')
                ->label('Valor')
                ->requiredMapping()
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return null;

                    // Se o valor já for numérico (ex: 14.96 ou 14), retorna direto como float
                    if (is_numeric($state)) {
                        return (float) $state;
                    }

                    // Caso seja string formatada em PT-BR (ex: "1.014,96" ou "14,96"):
                    // 1. Remove os pontos de milhar
                    // 2. Troca a vírgula decimal por ponto
                    $cleaned = str_replace('.', '', $state);
                    $cleaned = str_replace(',', '.', $cleaned);

                    return (float) $cleaned;
                })
                ->rules(['required', 'integer']),
            ImportColumn::make('transaction_date')
                ->label('Data da Transação')
                ->requiredMapping()
                ->rules(['required', 'date']),
        ];
    }

    public function resolveRecord(): Transaction
    {
        $linkedTransaction = LinkedTransaction::query()
            ->where('origin', $this->data['name'])
            ->first();

        $creditCard = $this->options['ownerRecord']->creditCard ?? null;

        $category = $linkedTransaction?->category ?? Category::query()->where('name', 'Compra')->first();

        return Transaction::create([
            'name' => $linkedTransaction?->alternative ?? $this->data['name'],
            'type' => $linkedTransaction?->category?->type ?? 'expense',
            'amount' => $this->data['amount'],
            'payment_method' => 'credit',
            'transaction_date' => $this->data['transaction_date'],
            'is_paid' => false,
            'account_id' => $creditCard->account?->id,
            'category_id' => $category?->id,
            'credit_card_id' => $creditCard?->id,
            'invoice_id' => $this->options['ownerRecord']->id
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your transaction import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
