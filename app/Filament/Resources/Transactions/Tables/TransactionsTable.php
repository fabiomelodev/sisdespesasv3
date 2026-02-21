<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Helpers\FormatCurrency;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Transaction::EXPENSE => 'Despesa',
                        Transaction::INCOME => 'Renda',
                        Transaction::RESERVE => 'Reserva',
                        Transaction::TRANSFER => 'Transferência',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Transaction::EXPENSE => 'danger',
                        Transaction::INCOME => 'success',
                        Transaction::RESERVE => 'info',
                        Transaction::TRANSFER => 'warning',
                    }),
                TextColumn::make('category.name')
                    ->label('Categoria'),
                TextColumn::make('account.name')
                    ->label('Conta Bancária'),
                TextColumn::make('payment_method')
                    ->label('Meio de Pagamento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'debit' => 'Débito',
                        'credit' => 'Crédito',
                        'pix' => 'Pix'
                    }),
                TextColumn::make('amount')->label('Valor')
                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                TextColumn::make('transaction_date')->label('Data da Transação')->date('d/m/Y'),
                ToggleColumn::make('is_paid')->label('Pago'),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        Transaction::INCOME => 'Renda',
                        Transaction::EXPENSE => 'Despesa',
                        Transaction::RESERVE => 'Reserva',
                        Transaction::TRANSFER => 'Transferência'
                    ]),
                SelectFilter::make('account_id')
                    ->label('Conta Bancária')
                    ->relationship('account', 'name'),
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),
                SelectFilter::make('is_paid')
                    ->label('Pago')
                    ->options([
                        0 => 'Pendente',
                        1 => 'Pago'
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
