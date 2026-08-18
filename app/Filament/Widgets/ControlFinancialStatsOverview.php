<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Invoice;
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
        // Usa whereNotNull (em vez de exigir a recorrência ainda ativa) para
        // ser o complemento exato de expensesPaidMonthCurrentSum acima: toda
        // transação com recurring_transaction_id cai aqui, mesmo que a
        // recorrência de origem tenha sido desativada depois. Sem isso, uma
        // transação assim nao contava em nenhuma das duas somas.
        $recurringTransactionsMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->where('payment_method', '!=', 'credit')
            ->whereNotNull('recurring_transaction_id')
            ->sum('amount');


        // --- Cálculo Final ---
        $remainingMonthCurrentSum = $incomesPaidMonthCurrentSum - $expensesInvoicesMonthCurrentSum - $recurringTransactionsMonthCurrentSum;

        // --- Projeção (considera o que ainda está pendente, como se tudo fosse pago) ---
        // Entradas: pagas + pendentes (ex: salário que ainda não caiu)
        $allIncomesMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isIncome()
            ->sum('amount');

        // Despesas avulsas em débito/pix (sem recorrência, sem fatura) ainda não pagas
        $pendingAdHocExpensesSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->whereIn('payment_method', ['debit', 'pix'])
            ->whereNull('recurring_transaction_id')
            ->notIsPaid()
            ->sum('amount');

        $projectedRemainingMonthCurrentSum = $allIncomesMonthCurrentSum
            - $expensesInvoicesMonthCurrentSum
            - $recurringTransactionsMonthCurrentSum
            - $pendingAdHocExpensesSum;

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

            Stat::make('Livre Projetado', FormatCurrency::getFormatCurrency($projectedRemainingMonthCurrentSum))
                ->description('Considerando entradas e saídas ainda pendentes')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($projectedRemainingMonthCurrentSum >= 0 ? 'primary' : 'danger'),
        ];
    }
}