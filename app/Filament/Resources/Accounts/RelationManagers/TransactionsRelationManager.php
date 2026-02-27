<?php

namespace App\Filament\Resources\Accounts\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Helpers\FormatCurrency;
use App\Models\Transaction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $relatedResource = TransactionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Transaction::EXPENSE => 'Despesa',
                        Transaction::INCOME => 'Renda',
                        Transaction::GOAL => 'Meta',
                        Transaction::TRANSFER => 'Transferência',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Transaction::EXPENSE => 'danger',
                        Transaction::INCOME => 'success',
                        Transaction::GOAL => 'warning',
                        Transaction::TRANSFER => 'warning',
                    }),
                TextColumn::make('category.name')
                    ->label('Categoria'),
                TextColumn::make('payment_method')
                    ->label('Meio de Pagamento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'debit' => 'Débito',
                        'credit' => 'Crédito',
                        'pix' => 'Pix'
                    }),
                TextColumn::make('amount')->label('Valor')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('transaction_date')->label('Data da Transação')->date('d/m/Y'),
                ToggleColumn::make('is_paid')->label('Pago'),
            ]);
    }
}
