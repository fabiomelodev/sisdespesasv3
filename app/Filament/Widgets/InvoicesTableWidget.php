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
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class InvoicesTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 3;

    public function table(Table $table): Table
    {
        // 1. Captura as datas dos filtros globais
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        // 2. Construção da Query Base
        $query = Invoice::query()
            ->select('invoices.*')
            // Aplicando o filtro de data dinâmico
            ->when($startDate, fn($q) => $q->whereDate('due_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('due_date', '<=', $endDate))
            // Se não houver filtro, mantém o comportamento original (mês atual)
            ->when(!$startDate && !$endDate, fn($q) => $q->monthCurrent())

            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id')
                    ->where('type', 'expense');
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->orderBy('due_date', 'asc');

        // 3. Cálculo do valor total para a descrição
        // Usamos clonagem para não afetar a query principal da tabela
        $valueTotal = FormatCurrency::getFormatCurrency($query->clone()->get()->sum('totalExpenses'));

        return $table
            ->query(fn() => $query)
            ->heading('Faturas')
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