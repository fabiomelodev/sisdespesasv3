<?php

namespace App\Livewire;

use App\Models\Category;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CategoryExpenseAnalysisTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $now = Carbon::now();
        $mesAtual = $now->format('Y-m');
        $mesMenos1 = $now->copy()->subMonth()->format('Y-m');
        $mesMenos2 = $now->copy()->subMonths(2)->format('Y-m');

        $query = Category::query()
            ->select('categories.id', 'categories.name')
            ->join('transactions', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            ->selectRaw("
                        SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesMenos2}' THEN amount ELSE 0 END) as gasto_mes_2,
                        SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesMenos1}' THEN amount ELSE 0 END) as gasto_mes_1,
                        SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = '{$mesAtual}' THEN amount ELSE 0 END) as gasto_atual
                    ")
            ->groupBy('categories.id', 'categories.name');

        return $table
            ->query(fn(): Builder => $query)
            ->heading('Análise de Despesas por Categoria')
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')->label('Categoria'),

                TextColumn::make('gasto_mes_2')
                    ->label(fn() => $now->copy()->subMonths(2)->translatedFormat('M/Y'))
                    ->money('BRL'),
                TextColumn::make('gasto_mes_1')
                    ->label(fn() => $now->copy()->subMonth()->translatedFormat('M/Y'))
                    ->money('BRL'),
                TextColumn::make('gasto_atual')
                    ->label('Mês Atual')
                    ->money('BRL'),
                TextColumn::make('media')
                    ->label('Média (3 meses)')
                    ->state(fn($record) => ($record->gasto_mes_2 + $record->gasto_mes_1 + $record->gasto_atual) / 3)
                    ->money('BRL'),
                TextColumn::make('diferenca_percentual')
                    ->label('% vs Mês Ant.')
                    ->state(function ($record) {
                        if ($record->gasto_mes_1 <= 0)
                            return '0%';
                        $diff = (($record->gasto_atual - $record->gasto_mes_1) / $record->gasto_mes_1) * 100;
                        return number_format($diff, 1) . '%';
                    })
                    ->color(fn($state) => str_contains($state, '-') ? 'success' : 'danger') // Verde se gastou menos, vermelho se mais
                    ->icon(fn($state) => str_contains($state, '-') ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up'),
            ]);
    }
}
