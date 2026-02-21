<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecurringTransactionsTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        $lastMonth = now()->subMonthNoOverflow();

        $currentMonth = now();

        $query = Transaction::query()
            ->where(function (Builder $query) use ($lastMonth, $currentMonth) {
                $query->where(function (Builder $q) use ($lastMonth) {
                    $q->where('payment_method', 'credit')
                        ->whereMonth('transaction_date', $lastMonth->month)
                        ->whereYear('transaction_date', $lastMonth->year);
                })
                    ->orWhere(function (Builder $q) use ($currentMonth) {
                        $q->where('payment_method', '!=', 'credit')
                            ->whereMonth('transaction_date', $currentMonth->month)
                            ->whereYear('transaction_date', $currentMonth->year);
                    });
            })
            ->typeExpense()
            ->with('recurringTransaction')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->where('is_active', 1);
            })
            ->orderBy('is_paid', 'desc');

        $transactionsPedingSum = Transaction::query()
            ->where('is_paid', 0)
            ->where(function (Builder $query) use ($lastMonth, $currentMonth) {
                $query->where(function (Builder $q) use ($lastMonth) {
                    $q->where('payment_method', 'credit')
                        ->whereMonth('transaction_date', $lastMonth->month)
                        ->whereYear('transaction_date', $lastMonth->year);
                })
                    ->orWhere(function (Builder $q) use ($currentMonth) {
                        $q->where('payment_method', '!=', 'credit')
                            ->whereMonth('transaction_date', $currentMonth->month)
                            ->whereYear('transaction_date', $currentMonth->year);
                    });
            })
            ->typeExpense()
            ->with('recurringTransaction')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->where('is_active', 1);
            })
            ->orderBy('is_paid', 'desc')
            ->sum('amount');

        $transactionsPaidSum = Transaction::query()
            ->where('is_paid', 1)
            ->where(function (Builder $query) use ($lastMonth, $currentMonth) {
                $query->where(function (Builder $q) use ($lastMonth) {
                    $q->where('payment_method', 'credit')
                        ->whereMonth('transaction_date', $lastMonth->month)
                        ->whereYear('transaction_date', $lastMonth->year);
                })
                    ->orWhere(function (Builder $q) use ($currentMonth) {
                        $q->where('payment_method', '!=', 'credit')
                            ->whereMonth('transaction_date', $currentMonth->month)
                            ->whereYear('transaction_date', $currentMonth->year);
                    });
            })
            ->typeExpense()
            ->with('recurringTransaction')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->where('is_active', 1);
            })
            ->orderBy('is_paid', 'desc')
            ->sum('amount');

        $description = 'Pendente: ' . FormatCurrency::getFormatCurrency($transactionsPedingSum) . ' Pago: ' . FormatCurrency::getFormatCurrency($transactionsPaidSum);

        return $table
            ->query(fn(): Builder => $query)
            ->heading('Transações Recorrentes')
            ->description($description)
            ->searchable(false)
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->weight(FontWeight::Bold)
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state))
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->label('Data da Transação')
                    ->date('d/m/Y')
                    ->sortable(),
                ToggleColumn::make('is_paid')
                    ->label('Pago')
                    ->onColor('success')
                    ->offColor('danger')
            ]);
    }
}
