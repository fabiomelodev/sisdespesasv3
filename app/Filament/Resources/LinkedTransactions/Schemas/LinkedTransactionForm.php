<?php

namespace App\Filament\Resources\LinkedTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LinkedTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->columns(2)
                    ->schema([
                        TextInput::make('origin')
                            ->label('Origem')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Texto exatamente como aparece na fatura importada (ex: "Dl*Uberrides").'),
                        TextInput::make('alternative')
                            ->label('Alternativa')
                            ->required()
                            ->helperText('Nome amigável que substituirá a origem ao importar (ex: "Uber").'),
                    ]),
                Section::make()
                    ->columnSpan(3)
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->required()
                            ->helperText('Categoria aplicada automaticamente às transações importadas com essa origem.'),
                    ]),
            ]);
    }
}
