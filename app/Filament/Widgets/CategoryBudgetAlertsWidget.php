<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class CategoryBudgetAlertsWidget extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.category-budget-alerts';

    protected int|string|array $columnSpan = 1;

    public function getAlerts(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $categories = Category::query()
            ->isExpense()
            ->where('limit', '>', 0)
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
            ->get();

        return $categories
            ->map(function (Category $category): array {
                $percentage = $category->limit > 0
                    ? ($category->totalExpenses / $category->limit) * 100
                    : 0;

                return [
                    'name' => $category->name,
                    'spent' => $category->totalExpenses,
                    'limit' => $category->limit,
                    'percentage' => $percentage,
                ];
            })
            ->filter(fn(array $alert): bool => $alert['percentage'] >= 80)
            ->sortByDesc('percentage')
            ->values()
            ->all();
    }
}
