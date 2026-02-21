<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('current_amount')
                                    ->label('Valor Atual')
                                    ->prefix('R$')
                                    ->disabled(),
                                TextInput::make('target_amount')
                                    ->label('Valor Desejado')
                                    ->prefix('R$')
                                    ->required(),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name')
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->label('Data Inicial'),
                                DatePicker::make('end_date')
                                    ->label('Data Final'),
                                Toggle::make('is_active')
                                    ->label('Ativo')
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
