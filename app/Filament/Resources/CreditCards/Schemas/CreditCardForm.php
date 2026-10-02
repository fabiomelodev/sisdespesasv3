<?php

namespace App\Filament\Resources\CreditCards\Schemas;

use App\Helpers\DateHelper;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreditCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->columnSpanFull()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Toggle::make('dynamic_closing')
                            ->label('Fechamento dinâmico')
                            ->live()
                            ->dehydrated(false)
                            ->default(false)
                            ->afterStateHydrated(fn(Toggle $component, ?Model $record) => $component->state($record?->closing_offset_days !== null))
                            ->columnSpanFull()
                            ->helperText('Para cartões cujo fechamento varia com o tamanho do mês (ex: fecha sempre N dias corridos antes do vencimento) em vez de um dia fixo do mês.'),
                        Select::make('opening_day')
                            ->label('Abertura')
                            ->options(DateHelper::getDays())
                            ->columnSpan(1)
                            ->default('01')
                            ->visible(fn(Get $get): bool => !$get('dynamic_closing'))
                            ->required(fn(Get $get): bool => !$get('dynamic_closing'))
                            ->rules(fn(Get $get): array => $get('dynamic_closing') ? [] : ['different:closing_day'])
                            ->dehydrateStateUsing(fn(Get $get, ?string $state) => $get('dynamic_closing') ? null : $state)
                            ->dehydratedWhenHidden()
                            ->helperText('Dia que começa a contar as transações do ciclo.'),
                        Select::make('closing_day')
                            ->label('Fechamento')
                            ->options(DateHelper::getDays())
                            ->columnSpan(1)
                            ->default('01')
                            ->visible(fn(Get $get): bool => !$get('dynamic_closing'))
                            ->required(fn(Get $get): bool => !$get('dynamic_closing'))
                            ->rules(fn(Get $get): array => $get('dynamic_closing') ? [] : ['different:opening_day'])
                            ->dehydrateStateUsing(fn(Get $get, ?string $state) => $get('dynamic_closing') ? null : $state)
                            ->dehydratedWhenHidden()
                            ->helperText('Dia que encerra a contagem do ciclo.'),
                        TextInput::make('closing_offset_days')
                            ->label('Fecha (dias antes do vencimento)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->columnSpan(1)
                            ->visible(fn(Get $get): bool => (bool) $get('dynamic_closing'))
                            ->required(fn(Get $get): bool => (bool) $get('dynamic_closing'))
                            ->dehydrateStateUsing(fn(Get $get, $state) => $get('dynamic_closing') ? $state : null)
                            ->dehydratedWhenHidden()
                            ->helperText('Ex: 7 = fecha 7 dias corridos antes do vencimento.'),
                        Select::make('due_day')
                            ->label('Vencimento')
                            ->options(DateHelper::getDays())
                            ->columnSpan(1)
                            ->required(),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('limit')
                                    ->label('Limite')
                                    ->prefix('R$')
                                    ->required(),
                                Select::make('account_id')
                                    ->label('Conta Bancária')
                                    ->relationship('account', 'name', fn(Builder $query): Builder => $query->where('status', true))
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Ativo')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->required()
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
