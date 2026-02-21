<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Builder;

class TransactionsChartWidget extends ChartWidget
{
    use HasFiltersSchema;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Transações';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Tipo')
                ->options([
                    'expense' => 'Despesa',
                    'income' => 'Renda',
                    'transfer' => 'Transferência'
                ]),
            Select::make('category_id')
                ->label('Categoria')
                ->options(function () {
                    return Category::orderBy('name', 'asc')->pluck('name', 'id');
                })
        ]);
    }

    protected function getData(): array
    {
        $query = Transaction::query()
            // ->monthCurrent()
            ->isPaid();

        // ->when($this->filters['type'], function (Builder $query): Builder {
        //     return $query->where('type', $this->filters['type']);
        // });

        $data = Trend::query($query)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Transações',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
