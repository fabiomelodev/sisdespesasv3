<?php

namespace App\Filament\Resources\ReservationTransactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ReservationTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Select::make('reservation_id')
                            ->label('Caixinha')
                            ->relationship('reservation', 'name')
                            ->required(),
                    ]),
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Valor')
                                    ->prefix('R$')
                                    ->required(),
                                DatePicker::make('transaction_date')
                                    ->label('Data da Transação')
                                    ->required(),
                                Toggle::make('is_paid')
                                    ->label('Pago')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->required()
                            ]),
                        Section::make()
                            ->hidden(fn(?Model $record) => $record === null)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Criado Em')
                                    ->state(state: fn(Model $record): ?string => $record->created_at?->diffForHumans()),

                                TextEntry::make('updated_at')
                                    ->label('Modificado Em')
                                    ->state(fn(Model $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                    ])
            ]);
    }
}
