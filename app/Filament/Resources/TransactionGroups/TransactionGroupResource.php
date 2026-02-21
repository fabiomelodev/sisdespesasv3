<?php

namespace App\Filament\Resources\TransactionGroups;

use App\Filament\Resources\TransactionGroups\Pages\CreateTransactionGroup;
use App\Filament\Resources\TransactionGroups\Pages\EditTransactionGroup;
use App\Filament\Resources\TransactionGroups\Pages\ListTransactionGroups;
use App\Filament\Resources\TransactionGroups\Pages\ViewTransactionGroup;
use App\Filament\Resources\TransactionGroups\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\TransactionGroups\Schemas\TransactionGroupForm;
use App\Filament\Resources\TransactionGroups\Tables\TransactionGroupsTable;
use App\Models\TransactionGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransactionGroupResource extends Resource
{
    protected static ?string $model = TransactionGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $recordTitleAttribute = 'TransactionGroup';

    protected static ?string $label = 'Compra Parcelada';

    protected static ?string $pluralLabel = 'Compras Parceladas';

    protected static string|UnitEnum|null $navigationGroup = 'Transações';

    public static function form(Schema $schema): Schema
    {
        return TransactionGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactionGroups::route('/'),
            'create' => CreateTransactionGroup::route('/create'),
            'view' => ViewTransactionGroup::route('/{record}')
            // 'edit' => EditTransactionGroup::route('/{record}/edit'),
        ];
    }
}
