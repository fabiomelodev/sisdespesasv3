<?php

namespace App\Livewire;

use App\Helpers\FormatCurrency;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CategoryExpenseAnalysisTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // 1. Determinar a data de referência (fim do intervalo filtrado ou hoje)
        $endDate = $this->pageFilters['endDate'] ?? null;
        $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        // 2. Calcular os 3 meses baseados na referência
        $mesAtualRaw = $referenceDate->copy();
        $mesMenos1Raw = $referenceDate->copy()->subMonthsNoOverflow(1);
        $mesMenos2Raw = $referenceDate->copy()->subMonthsNoOverflow(2);

        $mesAtual = $mesAtualRaw->format('Y-m');
        $mesMenos1 = $mesMenos1Raw->format('Y-m');
        $mesMenos2 = $mesMenos2Raw->format('Y-m');

        // 3. Query com as somas condicionais
        $query = Category::query()
            ->select('categories.id', 'categories.name', 'categories.limit')
            ->join('transactions', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            // Filtramos a query principal para pegar apenas o range total dos 3 meses
            // Isso otimiza a performance do banco
            ->where('transaction_date', '>=', $mesMenos2Raw->startOfMonth()->toDateString())
            ->where('transaction_date', '<=', $mesAtualRaw->endOfMonth()->toDateString())
            ->selectRaw("
                SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesMenos2}' THEN amount ELSE 0 END) as gasto_mes_2,
                SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesMenos1}' THEN amount ELSE 0 END) as gasto_mes_1,
                SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesAtual}' THEN amount ELSE 0 END) as gasto_atual
            ")
            ->groupBy('categories.id', 'categories.name', 'categories.limit');

        return $table
            ->query(fn(): Builder => $query)
            ->heading('Análise de Despesas por Categoria')
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Categoria')
                    ->searchable(),

                TextColumn::make('gasto_mes_2')
                    ->label($mesMenos2Raw->translatedFormat('M/Y'))
                    ->money('BRL'),

                TextColumn::make('gasto_mes_1')
                    ->label($mesMenos1Raw->translatedFormat('M/Y'))
                    ->money('BRL'),

                TextColumn::make('gasto_atual')
                    ->label($endDate ? $mesAtualRaw->translatedFormat('M/Y') : 'Mês Atual')
                    ->money('BRL')
                    ->weight(fn($record) => $record->limit > 0 && $record->gasto_atual >= $record->limit * 0.8
                        ? \Filament\Support\Enums\FontWeight::Bold
                        : null)
                    ->color(function ($record): string {
                        if ($record->limit <= 0) {
                            return 'gray';
                        }

                        return match (true) {
                            $record->gasto_atual >= $record->limit => 'danger',
                            $record->gasto_atual >= $record->limit * 0.8 => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('limit')
                    ->label('Orçamento')
                    ->placeholder('—')
                    ->formatStateUsing(fn(?string $state): ?string => (float) $state > 0 ? FormatCurrency::getFormatCurrency($state) : null),

                TextColumn::make('media')
                    ->label('Média (Período)')
                    ->state(fn($record) => ($record->gasto_mes_2 + $record->gasto_mes_1 + $record->gasto_atual) / 3)
                    ->money('BRL'),

                TextColumn::make('diferenca_percentual')
                    ->label('% vs Mês Ant.')
                    ->state(function ($record) {
                        if ($record->gasto_mes_1 <= 0)
                            return '0%';
                        $diff = (($record->gasto_atual - $record->gasto_mes_1) / $record->gasto_mes_1) * 100;
                        return ($diff > 0 ? '+' : '') . number_format($diff, 1, ',', '.') . '%';
                    })
                    ->color(fn($state) => str_contains($state, '-') ? 'success' : ($state === '0%' ? 'gray' : 'danger'))
                    ->icon(fn($state) => str_contains($state, '-') ? 'heroicon-m-arrow-trending-down' : ($state === '0%' ? null : 'heroicon-m-arrow-trending-up')),
            ]);
    }
}
