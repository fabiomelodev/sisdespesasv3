<?php

namespace App\Filament\Resources\TransactionGroups\Tables;

use App\Helpers\FormatCurrency;
use App\Models\TransactionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->label('Cartão de Crédito')
                    ->placeholder('—'),
                TextColumn::make('payment_method')
                    ->label('Meio de Pagamento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'debit' => 'Débito',
                        'credit' => 'Crédito',
                        'pix' => 'Pix'
                    }),
                TextColumn::make('installments')
                    ->label('Parcelas')
                    ->sortable(),
                TextColumn::make('paid_installments')
                    ->label('Parcelas Pagas')
                    ->getStateUsing(function (TransactionGroup $record): string {
                        $paid = $record->transactions()->where('is_paid', true)->count();

                        return "{$paid}/{$record->installments}";
                    })
                    ->color(function (TransactionGroup $record): string {
                        $paid = $record->transactions()->where('is_paid', true)->count();

                        return match (true) {
                            $paid === 0 => 'gray',
                            $paid === (int) $record->installments => 'success',
                            default => 'warning',
                        };
                    }),
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
                SelectFilter::make('payment_method')
                    ->label('Meio de Pagamento')
                    ->options([
                        'debit' => 'Débito',
                        'credit' => 'Crédito',
                        'pix' => 'Pix',
                    ]),
                SelectFilter::make('is_paid')
                    ->label('Pago')
                    ->options([
                        1 => 'Sim',
                        0 => 'Não',
                    ]),
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
