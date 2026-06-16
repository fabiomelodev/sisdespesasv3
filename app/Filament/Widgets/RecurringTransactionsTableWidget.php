<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Transaction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RecurringTransactionsTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // 1. Captura as datas dos filtros globais
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        // 2. Escopo de consulta reutilizável para evitar repetição de código (DRY)
        $applyFilters = function (Builder $query) use ($startDate, $endDate) {
            $query->isExpense()
                ->where('payment_method', '!=', 'credit')
                ->whereHas('recurringTransaction', fn(Builder $q) => $q->where('is_active', 1))
                ->where(function (Builder $subQuery) use ($startDate, $endDate) {
                    if ($startDate || $endDate) {
                        // Se houver filtro global, ignora a lógica de "mês anterior vs atual" 
                        // e foca no intervalo selecionado
                        $subQuery->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
                            ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate));
                    } else {
                        // Comportamento padrão (Fallback) quando não há filtro selecionado
                        $lastMonth = now()->subMonthNoOverflow();
                        $currentMonth = now();

                        $subQuery->where(function (Builder $q) use ($lastMonth) {
                            $q->where('payment_method', 'credit')
                                ->whereMonth('transaction_date', $lastMonth->month)
                                ->whereYear('transaction_date', $lastMonth->year);
                        })->orWhere(function (Builder $q) use ($currentMonth) {
                            $q->where('payment_method', '!=', 'credit')
                                ->whereMonth('transaction_date', $currentMonth->month)
                                ->whereYear('transaction_date', $currentMonth->year);
                        });
                    }
                });
        };

        // 3. Execução das queries
        $baseQuery = Transaction::query()->with('recurringTransaction');

        $applyFilters($baseQuery); // Aplica os filtros na query da tabela

        // Queries para o cabeçalho (Somas)
        $transactionsPedingSum = Transaction::query()->notIsPaid()->tap($applyFilters)->sum('amount');
        $transactionsPaidSum = Transaction::query()->isPaid()->tap($applyFilters)->sum('amount');

        $description = 'Pendente: ' . FormatCurrency::getFormatCurrency($transactionsPedingSum) .
            ' | Pago: ' . FormatCurrency::getFormatCurrency($transactionsPaidSum);

        return $table
            ->query(fn() => $baseQuery->orderBy('is_paid', 'desc'))
            ->heading('Transações Recorrentes no Débito')
            ->description($description)
            ->searchable(false)
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(fn(string $state): string => Str::limit($state, 20))
                    ->size(TextSize::ExtraSmall)
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state))
                    ->size(TextSize::ExtraSmall)
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->label('Data')
                    ->date('d/m/y')
                    ->size(TextSize::ExtraSmall)
                    ->sortable(),
                ToggleColumn::make('is_paid')
                    ->label('Pago')
                    ->onColor('success')
                    ->offColor('danger')
            ]);
    }
}