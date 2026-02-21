<?php

namespace App\Livewire;

use App\Helpers\FormatCurrency;
use App\Models\RecurringTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecurringTransactionsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $recurringTransactionsExpenseSum = RecurringTransaction::isActive()->expense();

        $recurringTransactionsIncomeSum = RecurringTransaction::isActive()->income();

        return [
            Stat::make('Renda', FormatCurrency::getFormatCurrency($recurringTransactionsIncomeSum->sum('amount')))
                ->description('Quantidade Total: ' . $recurringTransactionsIncomeSum->count()),
            Stat::make('Despesa', FormatCurrency::getFormatCurrency($recurringTransactionsExpenseSum->sum('amount')))
                ->description('Quantidade Total: ' . $recurringTransactionsExpenseSum->count()),
        ];
    }
}
