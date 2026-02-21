<?php

namespace App\Filament\Resources\CreditCards\Schemas;

use App\Helpers\DateHelper;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->columnSpanFull()
                            ->required(),
                        Select::make('closing_day')
                            ->label('Fechamento')
                            ->options(DateHelper::getDays())
                            ->columnSpan(1)
                            ->required(),
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
                                    ->relationship('account', 'name')
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
