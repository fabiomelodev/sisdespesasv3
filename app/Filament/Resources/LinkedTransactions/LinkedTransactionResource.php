<?php

namespace App\Filament\Resources\LinkedTransactions;

use App\Filament\Resources\LinkedTransactions\Pages\CreateLinkedTransaction;
use App\Filament\Resources\LinkedTransactions\Pages\EditLinkedTransaction;
use App\Filament\Resources\LinkedTransactions\Pages\ListLinkedTransactions;
use App\Filament\Resources\LinkedTransactions\Schemas\LinkedTransactionForm;
use App\Filament\Resources\LinkedTransactions\Tables\LinkedTransactionsTable;
use App\Models\LinkedTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LinkedTransactionResource extends Resource
{
    protected static ?string $model = LinkedTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsUpDown;

    protected static ?string $recordTitleAttribute = 'LinkedTransaction';

    protected static ?string $label = 'Transação Vinculada';

    protected static ?string $pluralLabel = 'Transações Vinculadas';

    protected static string|UnitEnum|null $navigationGroup = 'Transações';

    public static function form(Schema $schema): Schema
    {
        return LinkedTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LinkedTransactionsTable::configure($table);
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
            'index' => ListLinkedTransactions::route('/'),
            'create' => CreateLinkedTransaction::route('/create'),
            'edit' => EditLinkedTransaction::route('/{record}/edit'),
        ];
    }
}
