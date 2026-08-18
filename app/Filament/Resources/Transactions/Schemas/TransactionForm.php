<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\CreditCard;
use App\Models\Invoice;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state == 'credit') {
                                    $set('type', Transaction::EXPENSE);
                                } else {
                                    $set('type', '');
                                }
                            })
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
                                    ->relationship('creditCard', 'name', fn(Builder $query): Builder => $query->where('is_active', true))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $creditCard = CreditCard::find($state);

                                        $set('account_id', $creditCard->account->id);
                                    }),
                                Select::make('transaction_group_id')
                                    ->label('Compra Parcelada')
                                    ->relationship('transactionGroup', 'name')
                                    ->visible(fn(?Model $record): bool => $record?->transaction_group_id !== null)
                                    ->disabled()
                                    ->helperText('Definida automaticamente ao criar uma Compra Parcelada.'),
                                TextInput::make('installment_number')
                                    ->label('Parcela')
                                    ->numeric()
                                    ->visible(fn(?Model $record): bool => $record?->transaction_group_id !== null)
                                    ->disabled()
                            ]),
                        Fieldset::make('Fatura')
                            ->visible(fn(?Model $record, Get $get): bool => $record !== null && $get('payment_method') === 'credit')
                            ->columns(2)
                            ->schema([
                                Toggle::make('override_invoice')
                                    ->label('Alterar fatura manualmente')
                                    ->live()
                                    ->dehydrated(false)
                                    ->default(false),
                                Select::make('invoice_id')
                                    ->label('Fatura')
                                    ->relationship('invoice', 'reference_month', fn(Builder $query, Get $get): Builder => $query->where('credit_card_id', $get('credit_card_id')))
                                    ->getOptionLabelFromRecordUsing(fn(Invoice $record): string => ucfirst($record->reference_month->translatedFormat('F \d\e Y')) . ' — vence ' . $record->due_date->format('d/m/Y'))
                                    ->disabled(fn(Get $get): bool => !$get('override_invoice'))
                                    ->required()
                                    ->helperText('Por padrão, a fatura é escolhida automaticamente pela data da transação e o fechamento do cartão.'),
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
                                    ->numeric()
                                    ->required(),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->live()
                                    ->required()
                                    ->options([
                                        Transaction::EXPENSE => 'Despesa',
                                        Transaction::INCOME => 'Renda',
                                    ]),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name', fn(Builder $query): Builder => $query->where('status', true))
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name', fn(Builder $query, Get $get): Builder => $query->where('type', $get('type')))
                                    ->required(),
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
