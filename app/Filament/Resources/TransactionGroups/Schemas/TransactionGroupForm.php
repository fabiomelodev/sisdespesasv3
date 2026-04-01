<?php

namespace App\Filament\Resources\TransactionGroups\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionGroupForm
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
                            ->label('Nome'),
                        Textarea::make('description')
                            ->label('Descrição')
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Total')
                                    ->prefix('R$')
                                    ->required(),
                                TextInput::make('installments')
                                    ->label('Parcelas')
                                    ->required()
                                    ->numeric(),
                                DatePicker::make('purchase_date')
                                    ->label('Data da Compra')
                                    ->required(),
                                Select::make('payment_method')
                                    ->label('Meio de Pagamento')
                                    ->required()
                                    ->live()
                                    ->options([
                                        'debit' => 'Débito',
                                        'credit' => 'Crédito',
                                        'pix' => 'Pix'
                                    ]),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name')
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name', fn(Builder $query): Builder => $query->isExpense())
                                    ->required(),
                                Select::make('credit_card_id')
                                    ->label('Cartão de Crédito')
                                    ->relationship('creditCard', 'name')
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
