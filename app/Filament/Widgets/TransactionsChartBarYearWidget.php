<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;

class TransactionsChartBarYearWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'Rendas x Despesas | Anual';

    protected function getData(): array
    {
        $incomeData = Trend::query(Transaction::where('type', 'income'))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->dateColumn('transaction_date')
            ->sum('amount');

        $expenseData = Trend::query(Transaction::where('type', 'expense'))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->dateColumn('transaction_date')
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $incomeData->map(fn($value) => $value->aggregate),
                    'backgroundColor' => '#bbf7d0',
                    'borderColor' => '#15803d',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Despesas',
                    'data' => $expenseData->map(fn($value) => $value->aggregate),
                    'backgroundColor' => '#fecaca',
                    'borderColor' => '#b91c1c',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $incomeData->map(fn($value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
