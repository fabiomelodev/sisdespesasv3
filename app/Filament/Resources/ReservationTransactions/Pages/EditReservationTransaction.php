<?php

namespace App\Filament\Resources\ReservationTransactions\Pages;

use App\Filament\Resources\ReservationTransactions\ReservationTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReservationTransaction extends EditRecord
{
    protected static string $resource = ReservationTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
