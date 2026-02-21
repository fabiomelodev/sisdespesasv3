<?php

namespace App\Filament\Resources\TransactionGroups\Pages;

use App\Filament\Resources\TransactionGroups\TransactionGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListTransactionGroups extends ListRecords
{
    protected static string $resource = TransactionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label(TransactionGroupResource::getLabel()),
        ];
    }
}
