<?php

namespace App\Filament\Resources\Goals\Tables;

use App\Helpers\FormatCurrency;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('target_amount')
                    ->label('Valor Desejado')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('balance')
                    ->label('Valor Atual')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'progress' => 'Em Andamento',
                        'completed' => 'Concluída',
                    })
                    ->colors([
                        'warning' => 'progress',
                        'success' => 'completed',
                    ]),
                TextColumn::make('start_date')
                    ->label('Data Inicial')
                    ->dateTime('d/m/Y'),
                TextColumn::make('end_date')
                    ->label('Data Final')
                    ->dateTime('d/m/Y'),
            ])
            ->filters([
                //
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
