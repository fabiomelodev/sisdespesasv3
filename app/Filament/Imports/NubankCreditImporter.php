<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Invoice;
use App\Models\LinkedTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class NubankCreditImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('transaction_date')
                ->label('Data')
                ->requiredMapping()
                ->rules(['required'])
                // Sem cast: o valor bruto é usado tanto para montar o nu_id
                // quanto para ser convertido manualmente em resolveRecord().
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('title')
                ->label('Título')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn() => null),
            ImportColumn::make('amount')
                ->label('Valor')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn() => null),
        ];
    }

    protected function parseAmount(string $raw): float
    {
        $raw = trim($raw);
        $isNegative = str_starts_with($raw, '-');
        $raw = ltrim($raw, '-');
        $raw = trim($raw);
        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);

        $value = (float) $raw;

        return $isNegative ? -$value : $value;
    }

    public function resolveRecord(): ?Transaction
    {
        $rawDate = trim($this->data['transaction_date']);
        $title = trim($this->data['title']);
        $rawAmount = trim($this->data['amount']);

        $identifier = $rawDate . '|' . $title . '|' . $rawAmount;

        if (Transaction::where('nu_id', $identifier)->exists()) {
            throw new RowImportFailedException('Transação já importada anteriormente (Identificador duplicado).');
        }

        $signedAmount = $this->parseAmount($rawAmount);

        // Numa fatura de cartão, valor positivo é compra (despesa) e valor
        // negativo é pagamento recebido/estorno (renda), ao contrário do
        // extrato de conta corrente.
        $type = $signedAmount < 0 ? Transaction::INCOME : Transaction::EXPENSE;

        $creditCard = CreditCard::query()->where('name', 'NuBank 0657')->first();

        $transactionDate = Carbon::parse($rawDate);

        // A transação só é "paga" se a fatura do ciclo correspondente já
        // estiver paga; caso contrário, fica pendente até a fatura ser
        // quitada (o is_paid não se resolve sozinho como no débito).
        $isPaid = false;

        if ($creditCard) {
            $closingDate = Invoice::closingDateForTransaction($transactionDate, $creditCard);

            $isPaid = (bool) $creditCard->invoices()
                ->whereMonth('reference_month', $closingDate->month)
                ->whereYear('reference_month', $closingDate->year)
                ->value('is_paid');
        }

        $linkedTransaction = LinkedTransaction::query()
            ->where('origin', $title)
            ->first();

        $category = $linkedTransaction?->category ?? ($type === Transaction::EXPENSE
            ? Category::query()->where('name', 'Compra')->first()
            : Category::firstOrCreate(['name' => 'Verificado', 'type' => Transaction::INCOME]));

        return Transaction::create([
            'name' => $linkedTransaction?->alternative ?? $title,
            'type' => $type,
            'amount' => abs($signedAmount),
            'payment_method' => 'credit',
            'transaction_date' => $transactionDate,
            'is_paid' => $isPaid,
            'account_id' => $creditCard?->account_id,
            'category_id' => $category?->id,
            'credit_card_id' => $creditCard?->id,
            'nu_id' => $identifier,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successfulRows = $import->successful_rows;

        $body = 'A importação da fatura de crédito NuBank foi concluída e ' . Number::format($successfulRows) . ' ' .
            ($successfulRows === 1 ? 'transação foi importada' : 'transações foram importadas') . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' .
                ($failedRowsCount === 1 ? 'linha foi ignorada/falhou' : 'linhas foram ignoradas/falharam') .
                ' (veja o motivo em cada linha).';
        }

        return $body;
    }
}
