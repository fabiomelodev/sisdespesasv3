<?php

namespace App\Filament\Resources\LinkedTransactions\Pages;

use App\Filament\Resources\LinkedTransactions\LinkedTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListLinkedTransactions extends ListRecords
{
    protected static string $resource = LinkedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label(label: LinkedTransactionResource::getLabel()),
        ];
    }
}
