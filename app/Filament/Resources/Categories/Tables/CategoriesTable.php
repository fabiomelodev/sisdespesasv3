<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Helpers\FormatCurrency;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
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
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => $state == 'income' ? 'Renda' : 'Despesa')
                    ->color(fn(string $state): string => $state == 'income' ? 'success' : 'danger'),
                TextColumn::make('limit')
                    ->label('Orçamento Mensal')
                    ->placeholder('—')
                    ->formatStateUsing(fn(?string $state): ?string => (float) $state > 0 ? FormatCurrency::getFormatCurrency($state) : null),
                TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y')
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'expense' => 'Despesa',
                        'income' => 'Renda',
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
