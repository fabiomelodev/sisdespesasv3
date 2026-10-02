<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoryStatsWidget extends StatsOverviewWidget
{
    public ?Category $record = null;

    protected function getStats(): array
    {
        $category = $this->record;

        $now = Carbon::now();
        $previousMonth = $now->copy()->subMonthsNoOverflow(1);

        $total = $category->transactions()->sum('amount');

        $currentMonthTotal = $category->transactions()
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        $previousMonthTotal = $category->transactions()
            ->whereMonth('transaction_date', $previousMonth->month)
            ->whereYear('transaction_date', $previousMonth->year)
            ->sum('amount');

        $color = $category->type === 'expense' ? 'danger' : 'success';

        return [
            Stat::make('Total da Categoria', FormatCurrency::getFormatCurrency($total))
                ->color($color),
            Stat::make('Mês Anterior', FormatCurrency::getFormatCurrency($previousMonthTotal))
                ->description($previousMonth->translatedFormat('F \d\e Y'))
                ->color($color),
            Stat::make('Mês Atual', FormatCurrency::getFormatCurrency($currentMonthTotal))
                ->description($now->translatedFormat('F \d\e Y'))
                ->color($color),
        ];
    }
}
