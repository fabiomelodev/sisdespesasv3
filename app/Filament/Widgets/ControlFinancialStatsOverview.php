<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Invoice;
use App\Models\ReservationTransaction;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ControlFinancialStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $incomesPaidMonthCurrentSum = Transaction::query()->monthCurrent()->typeIncome()->isPaid()->sum('amount');

        $invoicesMonthCurrentSum = Invoice::orderBy('due_date', 'asc')
            ->monthCurrent()
            ->select('invoices.*')
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(transactions.amount), 0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id')
                    ->where('transactions.type', 'expense')
                    ->whereNull('transactions.recurring_transaction_id');
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->sum('totalExpenses');

        $expensesPaidMonthCurrentSum = Transaction::query()
            ->monthCurrent()
            ->typeExpense()
            ->whereIn('payment_method', ['debit', 'pix'])
            ->whereNull('recurring_transaction_id')
            ->isPaid()
            ->sum('amount');

        $expensesInvoicesMonthCurrentSum = $invoicesMonthCurrentSum + $expensesPaidMonthCurrentSum;

        $recurringTransactionsMonthCurrentSum = Transaction::query()
            ->monthCurrent()
            ->typeExpense()
            ->with('recurringTransaction')
            ->orderBy('is_paid', 'desc')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->isActive();
            })
            ->sum('amount');

        $reservationTransactionsMonthCurrentSum = ReservationTransaction::query()->monthCurrent()->isPaid()->sum('amount');

        $remainingMonthCurrentSum = $incomesPaidMonthCurrentSum - $expensesInvoicesMonthCurrentSum - $recurringTransactionsMonthCurrentSum - $reservationTransactionsMonthCurrentSum;

        return [
            Stat::make('Entradas', FormatCurrency::getFormatCurrency($incomesPaidMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Saídas Realizadas', FormatCurrency::getFormatCurrency($expensesInvoicesMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
            Stat::make('Despesas Recorrentes', FormatCurrency::getFormatCurrency($recurringTransactionsMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
            Stat::make('Livre', FormatCurrency::getFormatCurrency($remainingMonthCurrentSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
        ];
    }
}
