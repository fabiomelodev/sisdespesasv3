<?php

namespace App\Filament\Resources\Invoices\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->defaultSort('due_date', 'desc')
            ->columns([
                TextColumn::make('creditCard.name')
                    ->label('Cartão de Crédito')
                    ->searchable(),
                TextColumn::make('reference_month')
                    ->label('Mês de Referência')
                    ->date('F Y'),
                TextColumn::make('closing_date')
                    ->label('Fechamento')
                    ->date('d/m/Y'),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y'),
                ToggleColumn::make('is_closed')
                    ->label('Fechado'),
                ToggleColumn::make('is_paid')
                    ->label('Pago'),
            ])
            ->filters([
                Filter::make('due_date')
                    ->schema([
                        Select::make('view')
                            ->label('Visualização')
                            ->default('all')
                            ->live()
                            ->options([
                                'all' => 'Todos',
                                'custom' => 'Personalizado'
                            ]),
                        DatePicker::make('date')
                            ->label('Vencimento')
                            ->native(false)
                            ->displayFormat('F Y')
                            ->visible(fn(Get $get): bool => $get('view') == 'custom' ? true : false)
                            ->default(''),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $startDate = null;

                        $endDate = null;

                        if ($data['date'] != '') {

                            $date = Carbon::create($data['date']);

                            $startDate = $date->copy()->startOfMonth();

                            $endDate = $date->copy()->endOfMonth();
                        }

                        return $query
                            ->when(
                                isset($startDate),
                                function (Builder $query, $date) use ($startDate): Builder {
                                    return $query->whereDate('due_date', '>=', $startDate);
                                },
                            )
                            ->when(
                                isset($endDate),
                                function (Builder $query) use ($endDate): Builder {
                                    return $query->whereDate('due_date', '<=', $endDate);
                                },
                            );
                    }),
                SelectFilter::make('credit_card_id')
                    ->label('Cartão de Crédito')
                    ->relationship('creditCard', 'name'),
                SelectFilter::make('is_closed')
                    ->label('Fechado')
                    ->options([
                        1 => 'Sim',
                        0 => 'Não'
                    ]),
                SelectFilter::make('is_paid')
                    ->label('Pago')
                    ->options([
                        1 => 'Pago',
                        0 => 'Pendente'
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
