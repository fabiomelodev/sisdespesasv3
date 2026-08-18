<?php

namespace App\Livewire;

use App\Helpers\FormatCurrency;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $expensesMonthCurrentSum = Transaction::isExpense()->isPaid()->monthCurrent()->sum('amount');

        $expensesSum = Transaction::isPaid()->isExpense()->sum('amount');

        $incomesMonthCurrentSum = Transaction::isIncome()->isPaid()->monthCurrent()->sum('amount');

        $incomesSum = Transaction::isPaid()->isIncome()->sum('amount');

        return [
            Stat::make('Entradas', FormatCurrency::getFormatCurrency($incomesMonthCurrentSum))
                ->description('Total ' . FormatCurrency::getFormatCurrency($incomesSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Saídas', FormatCurrency::getFormatCurrency($expensesMonthCurrentSum))
                ->description('Total ' . FormatCurrency::getFormatCurrency($expensesSum))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
