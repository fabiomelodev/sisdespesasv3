<?php

namespace App\Filament\Resources\TransactionGroups\Schemas;

use App\Helpers\FormatCurrency;
use App\Models\TransactionGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class TransactionGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columnSpan(9)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->label('Descrição')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Total')
                                    ->weight(FontWeight::Bold)
                                    ->formatStateUsing(fn(string $state): string => FormatCurrency::getFormatCurrency($state)),
                                TextEntry::make('installments')
                                    ->label('Parcelas'),
                                TextEntry::make('paid_installments')
                                    ->label('Parcelas Pagas')
                                    ->badge()
                                    ->state(function (TransactionGroup $record): string {
                                        $paid = $record->transactions()->where('is_paid', true)->count();

                                        return "{$paid}/{$record->installments}";
                                    })
                                    ->color(function (TransactionGroup $record): string {
                                        $paid = $record->transactions()->where('is_paid', true)->count();

                                        return match (true) {
                                            $paid === 0 => 'gray',
                                            $paid === (int) $record->installments => 'success',
                                            default => 'warning',
                                        };
                                    }),
                                TextEntry::make('purchase_date')
                                    ->label('Data da Compra')
                                    ->date('d/m/Y'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->label('Meio de Pagamento')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'debit' => 'Débito',
                                        'credit' => 'Crédito',
                                        'pix' => 'Pix',
                                    }),
                                TextEntry::make('account.name')
                                    ->label('Conta Bancária'),
                                TextEntry::make('category.name')
                                    ->label('Categoria'),
                                TextEntry::make('creditCard.name')
                                    ->label('Cartão de Crédito')
                                    ->visible(fn(TransactionGroup $record): bool => $record->credit_card_id !== null),
                            ]),
                    ]),
                Group::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextEntry::make('is_paid')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn(bool $state): string => $state ? 'Pago' : 'Pendente')
                                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                        Section::make()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Criado Em')
                                    ->state(fn(TransactionGroup $record): ?string => $record->created_at?->diffForHumans()),
                                TextEntry::make('updated_at')
                                    ->label('Modificado Em')
                                    ->state(fn(TransactionGroup $record): ?string => $record->updated_at?->diffForHumans()),
                            ]),
                    ]),
            ]);
    }
}
