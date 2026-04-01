<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatCurrency;
use App\Models\Goal;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentProgressBarColumn\Tables\Columns\ProgressBarColumn;

class GoalsTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => Goal::query()->isProgress()->limit(4))
            ->heading('Metas')
            ->paginated(false)
            ->headerActions(
                [
                    Action::make('action')
                        ->label('Ver Todos')
                        ->size(Size::Small)
                        ->url(route('filament.admin.resources.reservations.index'))
                ]
            )
            ->columns([
                TextColumn::make('name')
                    ->size(TextSize::ExtraSmall)
                    ->weight(FontWeight::Bold),
                Grid::make(2)
                    ->schema([
                        TextColumn::make('balance')
                            ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state)),
                        Stack::make([
                            TextColumn::make('target_amount')
                                ->formatStateUsing(fn($state) => FormatCurrency::getFormatCurrency($state))
                                ->grow(false),
                        ])->alignment(Alignment::End)
                    ]),
                ProgressBarColumn::make('percentage')
                    ->maxValue(100)
                    ->dangerColor('#ef4444')
                    ->warningColor('#f59e0b')
                    ->successColor('#22c55e')
                    ->dangerLabel('')
                    ->warningLabel('')
                    ->successLabel(''),
            ]);
    }
}
