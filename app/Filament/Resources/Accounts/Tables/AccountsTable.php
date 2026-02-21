<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Helpers\FormatCurrency;
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
                TextColumn::make('initial_balance')
                    ->label('Saldo Inicial')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('balance')
                    ->label('Saldo Atual')
                    ->formatStateUsing(callback: fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
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
                    ->label('Tipo')
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
