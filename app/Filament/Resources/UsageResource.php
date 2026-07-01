<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageResource\Pages;
use App\Models\Usage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use UsageForm;
use UsageInfolist;
use UsageTable;

class UsageResource extends Resource
{
    protected static ?string $model = Usage::class;

    public static function getNavigationLabel(): string
    {
        return 'Penggunaan Barang';
    }
    public static function getModelLabel(): string
    {
        return 'Penggunaan Barang';
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }
    public static function getPluralModelLabel(): string
    {
        return 'Penggunaan Barang';
    }
    public static function form(Schema $form): Schema
    {
        return UsageForm::configure($form);

    }

    public static function table(Table $table): Table
    {
        return UsageTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UsageInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            // Bisa tambahkan RelationManager jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsages::route('/'),
            'create' => Pages\CreateUsage::route('/create'),
            'view' => Pages\ViewUsage::route('/{record}'),
            'edit' => Pages\EditUsage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['usageItems.item.category', 'createdBy']);
    }
}