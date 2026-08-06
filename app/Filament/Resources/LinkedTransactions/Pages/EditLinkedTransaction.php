<?php

namespace App\Filament\Resources\LinkedTransactions\Pages;

use App\Filament\Resources\LinkedTransactions\LinkedTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLinkedTransaction extends EditRecord
{
    protected static string $resource = LinkedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
