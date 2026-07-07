<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Invoice;
use App\Models\ReservationTransaction;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ControlFinancialStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // 1. Captura as datas dos filtros
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        // 2. Helper para aplicar o filtro de data consistentemente em todas as queries
        // Substituímos o monthCurrent() por este filtro dinâmico
        $applyDateFilter = function (Builder $query, string $column = 'transaction_date') use ($startDate, $endDate) {
            return $query
                ->when($startDate, fn($q) => $q->whereDate($column, '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate($column, '<=', $endDate))
                // Se não houver filtro, você pode decidir se mantém o monthCurrent() como fallback
                ->when(!$startDate && !$endDate, fn($q) => $q->monthCurrent());
        };

        // --- Incomes ---
        $incomesPaidMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isIncome()
            ->isPaid()
            ->sum('amount');

        // --- Invoices (Despesas via Fatura) ---
        $invoicesMonthCurrentSum = Invoice::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'due_date')) // Filtrando pela data de vencimento
            ->select('invoices.*')
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(transactions.amount), 0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id');
                // ->whereNull('transactions.recurring_transaction_id');
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->get() // Usar get() antes do sum se houver 'having' complexo ou sum direto:
            ->sum('totalExpenses');

        // --- Expenses (Débito/Pix) ---
        $expensesPaidMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->whereIn('payment_method', ['debit', 'pix'])
            ->whereNull('recurring_transaction_id')
            ->isPaid()
            ->sum('amount');

        $expensesInvoicesMonthCurrentSum = $invoicesMonthCurrentSum + $expensesPaidMonthCurrentSum;

        // --- Recurring ---
        $recurringTransactionsMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->where('payment_method', '!=', 'credit')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->isActive();
            })
            ->sum('amount');


        // --- Cálculo Final ---
        $remainingMonthCurrentSum = $incomesPaidMonthCurrentSum - $expensesInvoicesMonthCurrentSum - $recurringTransactionsMonthCurrentSum;

        return [
            Stat::make('Entradas', FormatCurrency::getFormatCurrency($incomesPaidMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Saídas Realizadas', FormatCurrency::getFormatCurrency($expensesInvoicesMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Despesas Recorrentes', FormatCurrency::getFormatCurrency($recurringTransactionsMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Livre', FormatCurrency::getFormatCurrency($remainingMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}