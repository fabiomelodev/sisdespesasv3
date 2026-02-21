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
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TotalTransactionsExpenseByCategoriesTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $query = Category::orderBy('name', 'asc')
            ->select('categories.*')
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount),0)')
                    ->whereColumn('transactions.category_id', 'categories.id')
                    ->where('is_paid', 1)
                    ->where('type', 'expense')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0);

        $valueTotal = FormatCurrency::getFormatCurrency($query->get()->sum('totalExpenses'));


        return $table
            ->query(fn(): Builder => $query)
            ->heading('Despesas por Categorias')
            ->searchable(false)
            ->description('Total: ' . ($valueTotal))
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
                                ->grow(false)
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
