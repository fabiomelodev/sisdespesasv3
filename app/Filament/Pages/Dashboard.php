<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\{ControlFinancialStatsOverview, GoalsTableWidget, InvoicesTableWidget, RecurringTransactionsTableWidget, TotalTransactionsExpenseByCategoriesTableWidget, TransactionsChartBarYearWidget, TransactionsChartWidget};
use App\Livewire\CategoryExpenseAnalysisTable;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected int|string|array $columnSpan = 'full';

    public function getColumns(): int|array
    {
        return 3;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard Mensal';
    }

    public function getWidgets(): array
    {
        return [
            ControlFinancialStatsOverview::class,
            TransactionsChartBarYearWidget::class,
            GoalsTableWidget::class,
            RecurringTransactionsTableWidget::class,
            InvoicesTableWidget::class,
            TotalTransactionsExpenseByCategoriesTableWidget::class,
            CategoryExpenseAnalysisTable::class,
            TransactionsChartWidget::class,
        ];
    }
}