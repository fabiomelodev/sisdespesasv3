<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->schema([
                        Select::make('credit_card_id')
                            ->label('Cartão de Crédito')
                            ->relationship('creditCard', 'name')
                            ->required(),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                DatePicker::make('reference_month')
                                    ->label('Mês de Referência')
                                    ->required(),
                                TextEntry::make('closing_date')
                                    ->label('Fechamento')
                                    ->state(state: fn(Model $record): ?string => $record->closing_date->format('d/m/Y')),
                                DatePicker::make('due_date')
                                    ->label('Vencimento')
                                    ->required(),
                                Toggle::make('is_closed')
                                    ->label('Fechado')
                                    ->required(),
                                Toggle::make('is_paid')
                                    ->label('Pago')
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
