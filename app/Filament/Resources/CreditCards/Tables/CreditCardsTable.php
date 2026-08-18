<?php

namespace App\Filament\Resources\CreditCards\Tables;

use App\Helpers\FormatCurrency;
use App\Models\CreditCard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CreditCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('account.name')
                    ->label('Conta Bancária'),
                TextColumn::make('limit')
                    ->label('Limite')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('used_limit')
                    ->label('Limite Utilizado')
                    ->getStateUsing(function (CreditCard $record): float {
                        return (float) $record->invoices()
                            ->where('invoices.is_paid', false)
                            ->join('transactions', 'transactions.invoice_id', '=', 'invoices.id')
                            ->sum('transactions.amount');
                    })
                    ->formatStateUsing(fn(float $state): string => FormatCurrency::getFormatCurrency($state))
                    ->color(function (float $state, CreditCard $record): string {
                        if ($record->limit <= 0) {
                            return 'gray';
                        }

                        $percentage = ($state * 100) / $record->limit;

                        return match (true) {
                            $percentage >= 100 => 'danger',
                            $percentage >= 80 => 'warning',
                            default => 'success',
                        };
                    }),
                TextColumn::make('closing_day')
                    ->label('Fechamento'),
                TextColumn::make('due_day')
                    ->label('Vencimento'),
                ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->onColor('success')
                    ->offColor('danger')
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Ativo',
                        false => 'Inativo',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
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
