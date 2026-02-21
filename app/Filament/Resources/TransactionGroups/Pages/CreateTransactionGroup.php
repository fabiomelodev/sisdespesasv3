<?php

namespace App\Filament\Resources\TransactionGroups\Pages;

use App\Filament\Resources\TransactionGroups\TransactionGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionGroup extends CreateRecord
{
    protected static string $resource = TransactionGroupResource::class;
}
