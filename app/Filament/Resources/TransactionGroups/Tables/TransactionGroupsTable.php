<?php

namespace App\Filament\Resources\TransactionGroups\Tables;

use App\Helpers\FormatCurrency;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('account.name')
                    ->label('Conta Bancária'),
                TextColumn::make('creditCard.name')
                    ->label('Cartão de Crédito'),
                TextColumn::make('installments')
                    ->label('Parcelas')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('purchase_date')
                    ->label('Data da Compra')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('is_paid')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn($state): string => $state ? 'Sim' : 'Não')
                    ->color(fn($state): string => $state ? 'success' : 'danger')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
