<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages; // TAMBAHKAN INI
use App\Models\Purchase;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PurchaseForm;
use PurchaseInfolist;
use PurchaseTable;


class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengadaan Barang';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengadaan Barang';
    }


    public static function form(Schema $schema): Schema
    {
        return PurchaseForm::configure($schema);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return PurchaseInfolist::configure($infolist);
    }


    public static function table(Table $table): Table
    {
        return PurchaseTable::configure($table);

    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'createdBy',
                'period',
                'purchaseItems.item.category',
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}