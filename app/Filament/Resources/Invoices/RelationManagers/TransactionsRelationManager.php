<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Helpers\FormatCurrency;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Transações')
            ->defaultSort('installment_number', 'asc')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('transaction_date')
                    ->label('Data da Transação')
                    ->date('d/m/Y'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                IconColumn::make('is_paid')
                    ->label('Pago')
                    ->boolean()
            ]);
    }
}
