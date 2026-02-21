<?php

namespace App\Filament\Resources\RecurringTransactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecurringTransactionForm
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
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Tipo')
                            ->live()
                            ->required()
                            ->columnSpanFull()
                            ->options([
                                'expense' => 'Despesa',
                                'income' => 'Renda',
                            ]),
                        TextInput::make('due_day')
                            ->label('Dia do Vencimento')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        DatePicker::make('next_run_date')
                            ->label('Próxima Data')
                            ->columnSpan(1),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Valor')
                                    ->prefix('R$')
                                    ->required(),
                                Select::make('payment_method')
                                    ->label('Meio de Pagamento')
                                    ->options(['debit' => 'Débito', 'credit' => 'Crédito'])
                                    ->required(),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name')
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name', fn(Builder $query, Get $get): Builder => $query->where('type', $get('type')))
                                    ->required(),
                                Select::make('credit_card_id')
                                    ->label('Cartão de Crédito')
                                    ->relationship('creditCard', 'name'),
                                Toggle::make('is_active')
                                    ->label('Ativo')
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
