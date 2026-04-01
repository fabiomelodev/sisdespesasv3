<?php

namespace App\Livewire;

use App\Helpers\FormatCurrency;
use App\Models\Goal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GoalsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $goalsProgress = Goal::query()->isProgress()->count();

        $goalsProgressBalanceSum = Goal::query()->isProgress()->sum('balance');

        $goalsProgressTargetAmountSum = Goal::query()->isProgress()->sum('target_amount');

        $goalsCompleted = Goal::query()->isCompleted()->count();

        return [
            Stat::make('Metas em Progressos', $goalsProgress)
                ->description("Saldo: R$ " . FormatCurrency::getFormatCurrency($goalsProgressBalanceSum) . " / Meta: R$ " . FormatCurrency::getFormatCurrency($goalsProgressTargetAmountSum))
                ->icon('heroicon-o-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Metas Concluídas', $goalsCompleted)
                ->icon('heroicon-o-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info')
        ];
    }
}
