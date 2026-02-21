<?php

namespace App\Filament\Resources\CreditCards;

use App\Filament\Resources\CreditCards\Pages\CreateCreditCard;
use App\Filament\Resources\CreditCards\Pages\EditCreditCard;
use App\Filament\Resources\CreditCards\Pages\ListCreditCards;
use App\Filament\Resources\CreditCards\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\CreditCards\Schemas\CreditCardForm;
use App\Filament\Resources\CreditCards\Tables\CreditCardsTable;
use App\Models\CreditCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CreditCardResource extends Resource
{
    protected static ?string $model = CreditCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'CreditCard';

    protected static ?string $label = 'Cartão de Crédito';

    protected static ?string $pluralLabel = 'Cartões de Crédito';

    protected static string|UnitEnum|null $navigationGroup = 'Créditos';

    public static function form(Schema $schema): Schema
    {
        return CreditCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditCardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InvoicesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditCards::route('/'),
            'create' => CreateCreditCard::route('/create'),
            'edit' => EditCreditCard::route('/{record}/edit'),
        ];
    }
}
