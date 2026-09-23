<?php

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Category;
use App\Models\LinkedTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class NubankDebitImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('transaction_date')
                ->label('Data')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(fn(?string $state) => filled($state) ? Carbon::createFromFormat('d/m/Y', trim($state)) : null),
            ImportColumn::make('amount')
                ->label('Valor')
                ->requiredMapping()
                ->rules(['required', 'numeric'])
                ->castStateUsing(fn(?string $state) => is_numeric($state) ? abs((float) $state) : null),
            ImportColumn::make('nu_id')
                ->label('Identificador')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('description')
                ->label('Descrição')
                ->requiredMapping()
                ->rules(['required'])
                // "description" não é uma coluna de transactions: usada só para
                // extrair o nome do estabelecimento em resolveRecord().
                ->fillRecordUsing(fn() => null),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        $identifier = $this->data['nu_id'];

        if (Transaction::where('nu_id', $identifier)->exists()) {
            throw new RowImportFailedException('Transação já importada anteriormente (Identificador duplicado).');
        }

        $description = trim($this->data['description']);

        if (!str_starts_with(mb_strtolower($description), 'compra no débito')) {
            throw new RowImportFailedException('Ignorado: não é uma compra no débito.');
        }

        $merchant = trim(Str::after($description, '-'));

        $linkedTransaction = LinkedTransaction::query()
            ->where('origin', $merchant)
            ->first();

        $category = $linkedTransaction?->category ?? Category::query()->where('name', 'Compra')->first();

        $account = Account::query()->where('name', 'NuBank')->first();

        return Transaction::create([
            'name' => $linkedTransaction?->alternative ?? $merchant,
            'type' => Transaction::EXPENSE,
            'amount' => $this->data['amount'],
            'payment_method' => 'debit',
            'transaction_date' => $this->data['transaction_date'],
            'is_paid' => true,
            'account_id' => $account?->id,
            'category_id' => $category?->id,
            'nu_id' => $identifier,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successfulRows = $import->successful_rows;

        $body = 'A importação do extrato de débito NuBank foi concluída e ' . Number::format($successfulRows) . ' ' .
            ($successfulRows === 1 ? 'transação foi importada' : 'transações foram importadas') . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' .
                ($failedRowsCount === 1 ? 'linha foi ignorada/falhou' : 'linhas foram ignoradas/falharam') .
                ' (veja o motivo em cada linha).';
        }

        return $body;
    }
}
