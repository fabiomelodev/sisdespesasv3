<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Invoice;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $query = Invoice::orderBy('due_date', 'asc')
            ->monthCurrent()
            ->select('invoices.*')
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount),0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id')
                    ->where('type', 'expense');
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0);

        $valueTotal = FormatCurrency::getFormatCurrency($query->get()->sum('totalExpenses'));

        return $table
            ->query(fn(): Builder => $query)
            ->heading('Faturas')
            ->searchable(false)
            ->description('Total: ' . ($valueTotal))
            ->paginated(false)
            ->columns([
                Grid::make(2)
                    ->schema([
                        Stack::make([
                            TextColumn::make('creditCard.name')
                                ->label('Nome')
                                ->weight(FontWeight::Bold),
                            TextColumn::make('totalExpenses')
                                ->label('Total')
                                ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state))
                                ->size(TextSize::ExtraSmall),
                            TextColumn::make('due_date')
                                ->label('Vencimento')
                                ->date('d/m/Y')
                                ->size(TextSize::ExtraSmall)
                        ]),
                        Stack::make([
                            ToggleColumn::make('is_paid')
                                ->onColor('success')
                                ->offColor('danger')
                                ->grow(false)
                        ])->alignment(Alignment::Right),
                    ])
            ]);
    }
}
