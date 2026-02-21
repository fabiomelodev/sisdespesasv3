<?php

namespace App\Filament\Resources\ReservationTransactions;

use App\Filament\Resources\ReservationTransactions\Pages\CreateReservationTransaction;
use App\Filament\Resources\ReservationTransactions\Pages\EditReservationTransaction;
use App\Filament\Resources\ReservationTransactions\Pages\ListReservationTransactions;
use App\Filament\Resources\ReservationTransactions\Schemas\ReservationTransactionForm;
use App\Filament\Resources\ReservationTransactions\Tables\ReservationTransactionsTable;
use App\Models\ReservationTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReservationTransactionResource extends Resource
{
    protected static ?string $model = ReservationTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ReservationTransaction';

    protected static ?string $label = 'Transação';

    protected static ?string $pluralLabel = 'Transações';

    public static function form(Schema $schema): Schema
    {
        return ReservationTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            // 'index' => ListReservationTransactions::route('/'),
            // 'create' => CreateReservationTransaction::route('/create'),
            // 'edit' => EditReservationTransaction::route('/{record}/edit'),
        ];
    }
}
