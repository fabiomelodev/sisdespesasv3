<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Helpers\FormatCurrency;
use App\Models\Account;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'checking' => 'Corrente',
                        'savings' => 'Poupança',
                        'wallet' => 'Carteira',
                        'investment' => 'Investimento',
                    }),
                TextColumn::make('current_month_net')
                    ->label('Movimentação (Mês Atual)')
                    ->getStateUsing(function (Account $record): float {
                        $income = $record->transactions()->monthCurrent()->isPaid()->isIncome()->sum('amount');
                        $expense = $record->transactions()->monthCurrent()->isPaid()->isExpense()->sum('amount');

                        return $income - $expense;
                    })
                    ->formatStateUsing(fn(float $state): string => FormatCurrency::getFormatCurrency($state))
                    ->color(fn(float $state): string => $state >= 0 ? 'success' : 'danger'),
                IconColumn::make('status'),
                TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y')
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'checking' => 'Corrente',
                        'savings' => 'Poupança',
                        'wallet' => 'Carteira',
                        'investment' => 'Investimento',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Ativo',
                        false => 'Inativo',
                    ])
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
