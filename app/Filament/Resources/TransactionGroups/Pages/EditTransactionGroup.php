<?php

namespace App\Filament\Resources\TransactionGroups\Pages;

use App\Filament\Resources\TransactionGroups\TransactionGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionGroup extends EditRecord
{
    protected static string $resource = TransactionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
