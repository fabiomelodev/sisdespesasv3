<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Category;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class TotalTransactionsExpenseByCategoriesTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // 1. Captura as datas dos filtros globais
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        // 2. Construção da Query
        $query = Category::query()
            ->orderBy('name', 'asc')
            ->select('categories.*')
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.category_id', 'categories.id')
                    ->where('type', 'expense')
                    ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
                    ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
                    ->when(!$startDate && !$endDate, function ($q) {
                        $q->whereMonth('transaction_date', now()->month)
                            ->whereYear('transaction_date', now()->year);
                    });
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0);

        // 3. Cálculo do Total Geral e Porcentagem
        $categoriesData = $query->get();
        $grandTotal = $categoriesData->sum('totalExpenses');
        $valueTotalFormatted = FormatCurrency::getFormatCurrency($grandTotal);

        return $table
            ->query(fn() => $query)
            ->heading('Despesas por Categorias')
            ->description('Total: ' . $valueTotalFormatted)
            ->searchable(false)
            ->paginated(false)
            ->columns([
                Grid::make(2)
                    ->schema([
                        Stack::make([
                            TextColumn::make('name')
                                ->label('Nome')
                                ->weight(FontWeight::Bold),
                            TextColumn::make('totalExpenses')
                                ->label('Total')
                                ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state)),
                        ]),
                        Stack::make([
                            TextColumn::make('percentage')
                                ->state(function ($record) use ($grandTotal) {
                                    // Cálculo dinâmico da porcentagem baseada no total filtrado
                                    if ($grandTotal <= 0)
                                        return 0;
                                    return ($record->totalExpenses / $grandTotal) * 100;
                                })
                                ->formatStateUsing(fn($state): string => number_format($state, 0, ',', '.') . '%')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->color('danger')
                        ])->alignment(Alignment::Right),
                    ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ]);
    }
}