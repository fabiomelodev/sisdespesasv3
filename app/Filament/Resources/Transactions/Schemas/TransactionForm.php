<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionForm
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
                        DatePicker::make('transaction_date')
                            ->label('Data da Transação')
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
                        Fieldset::make('Cartão de Crédito')
                            ->visible(fn(Get $get): bool => $get('payment_method') == 'credit' ? true : false)
                            ->columns(3)
                            ->schema([
                                Select::make('credit_card_id')
                                    ->label('Cartão de Crédito')
                                    ->relationship('creditCard', 'name'),
                                Select::make('transaction_group_id')
                                    ->label('Compra Parcelada')
                                    ->relationship('transactionGroup', 'name'),
                                TextInput::make('installment_number')
                                    ->label('Parcela')
                                    ->numeric()
                            ])
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
                                Select::make('type')
                                    ->label('Tipo')
                                    ->live()
                                    ->required()
                                    ->options([
                                        'expense' => 'Despesa',
                                        'income' => 'Renda',
                                        'reserve' => 'Reserva',
                                        'transfer' => 'Transferência',
                                    ]),
                                Select::make(name: 'reservation_id')
                                    ->label('Caixinha')
                                    ->relationship('reservation', 'name')
                                    ->placeholder('Opcional')
                                    ->visible(fn(Get $get): bool => $get('type') === 'reserve')
                                    ->required(),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name')
                                    ->required(),
                                Select::make('destination_account_id')
                                    ->label('Conta Destino')
                                    ->relationship('destinationAccount', 'name')
                                    ->visible(fn(Get $get) => $get('type') === 'transfer')
                                    ->required(fn(Get $get) => $get('type') === 'transfer'),
                                Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name', fn(Builder $query, Get $get): Builder => $query->where('type', $get('type')))
                                    ->hidden(fn(Get $get): bool => match ($get('type')) {
                                        'expense' => false,
                                        'income' => false,
                                        'reserve' => true,
                                        'transfer' => true,
                                        default => false
                                    })
                                    ->required(fn(Get $get) => $get('type') !== 'transfer'),
                                Toggle::make('is_paid')
                                    ->label('Pago')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText(fn(Get $get): string => $get('payment_method') == 'credit' ? 'Pague a despesa pela fatura' : '')
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
