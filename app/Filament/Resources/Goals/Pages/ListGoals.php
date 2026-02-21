<?php

namespace App\Filament\Resources\Goals\Pages;

use App\Filament\Resources\Goals\GoalResource;
use App\Livewire\GoalsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListGoals extends ListRecords
{
    protected static string $resource = GoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label(label: GoalResource::getLabel()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GoalsStatsWidget::class
        ];
    }
}
