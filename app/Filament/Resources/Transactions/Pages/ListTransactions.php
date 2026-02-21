<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Livewire\TransactionStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionStats::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label(label: TransactionResource::getLabel()),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Todos'),
            'income' => Tab::make('Rendas')->icon(Heroicon::OutlinedArrowTrendingUp)->query(fn($query) => $query->typeIncome()),
            'expense' => Tab::make('Despesas')->icon(Heroicon::OutlinedArrowTrendingDown)->query(fn($query) => $query->typeExpense()),
            'transfer' => Tab::make('Transferências')->icon(Heroicon::OutlinedArrowTrendingUp)->query(fn($query) => $query->typeTransfer()),
            'reserve' => Tab::make('Reservas')->icon(Heroicon::OutlinedArrowTrendingUp)->query(fn($query) => $query->typeReserve()),
        ];
    }
}
