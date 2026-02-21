<?php

namespace App\Livewire;

use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoriesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $categoriesIncome = Category::query()->isIncome()->count();

        $categoriesExpense = Category::query()->isExpense()->count();

        return [
            Stat::make('Categorias de Entradas', $categoriesIncome)
                ->icon('heroicon-o-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Categorias de Saídas', $categoriesExpense)
                ->icon('heroicon-o-arrow-trending-down')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger')
        ];
    }
}
