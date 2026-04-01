<?php

namespace App\Filament\Resources\TransactionGroups\RelationManagers;

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

    protected static ?string $relatedResource = TransactionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('installment_number', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('invoice.reference_month')
                    ->label('Fatura')
                    ->date('d/m/Y'),
                TextColumn::make('installment_number')
                    ->label('Parcela'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                IconColumn::make('is_paid')
                    ->label('Pago')
                    ->boolean()
            ]);
    }
}
