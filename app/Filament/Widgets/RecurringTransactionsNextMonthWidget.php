<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\RecurringTransaction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class RecurringTransactionsNextMonthWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $nextMonth = now()->addMonthNoOverflow();

        $query = RecurringTransaction::query()
            ->isActive()
            ->expense()
            ->where('payment_method', '!=', 'credit');

        $total = (clone $query)->sum('amount');

        return $table
            ->query(fn() => $query)
            ->heading('Recorrentes Próximo Mês')
            ->description('Total: ' . FormatCurrency::getFormatCurrency($total))
            ->searchable(false)
            ->paginated(false)
            ->defaultSort('due_day', 'asc')
            ->columns([
                Grid::make(2)
                    ->schema([
                        Stack::make([
                            TextColumn::make('name')
                                ->label('Nome')
                                ->weight(FontWeight::Bold)
                                ->formatStateUsing(fn(string $state): string => Str::limit($state, 20))
                                ->size(TextSize::ExtraSmall),
                            TextColumn::make('amount')
                                ->label('Valor')
                                ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state))
                                ->size(TextSize::ExtraSmall),
                        ]),
                        Stack::make([
                            TextColumn::make('due_day')
                                ->label('Vencimento')
                                ->state(function (RecurringTransaction $record) use ($nextMonth): string {
                                    $day = min((int) $record->due_day, $nextMonth->daysInMonth);

                                    return $nextMonth->copy()->day($day)->format('d/m/Y');
                                })
                                ->size(TextSize::ExtraSmall),
                        ])->alignment(Alignment::Right),
                    ]),
            ]);
    }
}
