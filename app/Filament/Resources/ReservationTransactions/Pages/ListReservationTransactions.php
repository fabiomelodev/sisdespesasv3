<?php

namespace App\Filament\Resources\ReservationTransactions\Pages;

use App\Filament\Resources\ReservationTransactions\ReservationTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListReservationTransactions extends ListRecords
{
    protected static string $resource = ReservationTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label(ReservationTransactionResource::getLabel()),
        ];
    }
}
