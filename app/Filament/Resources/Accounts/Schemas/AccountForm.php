<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{ColorPicker, Select, TextInput, Toggle};
use Filament\Schemas\Components\{Group, Section};
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;

class AccountForm
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
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'checking' => 'Corrente',
                                        'savings' => 'Poupança',
                                        'wallet' => 'Carteira',
                                        'investment' => 'Investimento',
                                    ])
                                    ->default('checking')
                                    ->required(),
                                TextInput::make('initial_balance')
                                    ->label('Saldo Inicial')
                                    ->required()
                                    ->numeric(),
                                ColorPicker::make('color')
                                    ->label('Cor')
                                    ->required(),
                                Toggle::make('status')
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
