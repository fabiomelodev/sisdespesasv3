<?php

namespace App\Filament\Resources\ReservationTransactions\Pages;

use App\Filament\Resources\ReservationTransactions\ReservationTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReservationTransaction extends CreateRecord
{
    protected static string $resource = ReservationTransactionResource::class;
}
