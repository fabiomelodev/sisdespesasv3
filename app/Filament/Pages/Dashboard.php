<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\{ControlFinancialStatsOverview, InvoicesTableWidget, RecurringTransactionsTableWidget, TotalTransactionsExpenseByCategoriesTableWidget, TransactionsChartBarYearWidget, TransactionsChartWidget};
use App\Livewire\CategoryExpenseAnalysisTable;
use App\Livewire\InvoicesNextMontTableWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected int|string|array $columnSpan = 'full';

    public function getColumns(): int|array
    {
        return 3;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard Mensal';
    }



    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Data Inicial')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label('Data Final')
                            ->default(now()->endOfMonth()),
                    ])
            ]);
    }

    public function getWidgets(): array
    {
        return [
            ControlFinancialStatsOverview::class,
            RecurringTransactionsTableWidget::class,
            InvoicesTableWidget::class,
            InvoicesNextMontTableWidget::class,
            TransactionsChartBarYearWidget::class,
            TotalTransactionsExpenseByCategoriesTableWidget::class,
            CategoryExpenseAnalysisTable::class,
            // TransactionsChartWidget::class,
        ];
    }
}