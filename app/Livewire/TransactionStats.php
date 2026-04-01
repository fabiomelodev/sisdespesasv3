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
        $expensesMonthCurrentSum = Transaction::isExpense()->monthCurrent()->sum('amount');

        $expensesSum = Transaction::isPaid()->isExpense()->sum('amount');

        $incomesMonthCurrentSum = Transaction::isIncome()->monthCurrent()->sum('amount');

        $incomesSum = Transaction::isPaid()->isIncome()->sum('amount');

        $transfersMonthCurrentSum = Transaction::isTransfer()->monthCurrent()->sum('amount');

        $transfersSum = Transaction::isPaid()->isTransfer()->sum('amount');

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
            Stat::make('Transferências', FormatCurrency::getFormatCurrency($transfersMonthCurrentSum))
                ->description('Total ' . FormatCurrency::getFormatCurrency($transfersSum))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
        ];
    }
}
