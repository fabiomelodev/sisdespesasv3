<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Filament\Resources\ReservationTransactions\ReservationTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReservationTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservationTransactions';

    protected static ?string $relatedResource = ReservationTransactionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus)
                    ->label(ReservationTransactionResource::getLabel()),

            ]);
    }
}
