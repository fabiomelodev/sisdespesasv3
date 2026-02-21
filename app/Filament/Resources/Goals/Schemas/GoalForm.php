<?php

namespace App\Filament\Resources\Goals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class GoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('balance')
                                    ->label('Valor Atual')
                                    ->prefix('R$')
                                    ->disabled(),
                                TextInput::make('target_amount')
                                    ->label('Valor Desejado')
                                    ->prefix('R$')
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->label('Data Inicial'),
                                DatePicker::make('end_date')
                                    ->label('Data Final'),
                                Select::make('status')
                                    ->options(['progress' => 'Em Andamento', 'completed' => 'Concluída'])
                                    ->default('progress')
                                    ->required(),
                            ]),
                        Section::make()
                            ->hidden(fn(?Model $record) => $record === null)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Criado Em')
                                    ->state(state: fn(Model $record): ?string => $record->created_at?->diffForHumans()),

                                TextEntry::make('updated_at')
                                    ->label('Modificado Em')
                                    ->state(fn(Model $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                    ])
            ]);
    }
}
