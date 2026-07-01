<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemTypeResource\Pages;
use App\Models\ItemType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use ItemTypeForm;
use ItemTypeTable;


class ItemTypeResource extends Resource
{
    protected static ?string $model = ItemType::class;

    public static function getPluralLabel(): string
    {
        return 'Satuan Item';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-numbered-list';
    }

    public static function form(Schema $schema): Schema
    {
        return ItemTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemTypeTable::configure($table);
           
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount('items');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemTypes::route('/'),
        ];
    }
}
