<?php

namespace App\Livewire;

use App\Helpers\FormatCurrency;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class InvoicesNextMontTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        // Se houver filtro, usa a data final como base
        $baseDate = $endDate
            ? Carbon::parse($endDate)
            : now();

        // Próximo mês baseado na data escolhida
        $nextMonthStart = $baseDate->copy()->addMonthNoOverflow()->startOfMonth();
        $nextMonthEnd = $baseDate->copy()->addMonthNoOverflow()->endOfMonth();

        $query = Invoice::query()
            ->select('invoices.*')
            ->whereBetween('due_date', [$nextMonthStart, $nextMonthEnd])
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id')
                    ->where('type', 'expense');
            }, 'totalExpenses')

            ->having('totalExpenses', '>', 0)
            ->orderBy('due_date', 'asc');

        $valueTotal = FormatCurrency::getFormatCurrency(
            $query->clone()->get()->sum('totalExpenses')
        );

        return $table
            ->query(fn() => $query)
            ->heading('Faturas Próximo Mês')
            ->searchable(false)
            ->description('Total: ' . $valueTotal)
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
                                ->formatStateUsing(
                                    fn($state) => FormatCurrency::getFormatCurrency($state)
                                )
                                ->size(TextSize::ExtraSmall),

                            TextColumn::make('due_date')
                                ->label('Vencimento')
                                ->date('d/m/Y')
                                ->size(TextSize::ExtraSmall),
                        ]),

                        Stack::make([
                            ToggleColumn::make('is_paid')
                                ->onColor('success')
                                ->offColor('danger')
                                ->grow(false),
                        ])->alignment(Alignment::Right),
                    ]),
            ]);
    }
}
