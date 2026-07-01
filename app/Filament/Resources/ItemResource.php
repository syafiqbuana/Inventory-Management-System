<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\Pages\Schemas\ItemForm;
use App\Filament\Resources\ItemResource\Pages\Tables\ItemTable;
use App\Models\Item;
use App\Models\Period;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;



class ItemResource extends Resource
{
    protected static ?string $model = Item::class;


    public static function getNavigationLabel(): string
    {
        return 'Stok Awal & Data Barang';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Stok Awal & Data Barang';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-archive-box';
    }

    public static function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemTable::configure($table);
    }

    /**
     * Returns the relations available for the resource.
     *
     * @return array<string>
     */
    public static function getRelations(): array
    {
        // The relations available for the resource.
        return [

        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

        protected static ?int $activePeriodId = null;

    public static function getEloquentQuery(): Builder
    {
        if (static::$activePeriodId === null) {
            static::$activePeriodId = Period::query()
                ->where('is_closed', false)
                ->value('id');
        }

        return parent::getEloquentQuery()
            ->with(['category', 'createdBy', 'itemType'])
            ->withSum([
                'purchaseItems as purchased_qty' => fn($q) =>
                    $q->whereHas(
                        'purchase',
                        fn($p) =>
                        $p->where('period_id', static::$activePeriodId)
                    ),
            ], 'qty')
            ->withSum([
                'usageItems as used_qty' => fn($q) =>
                    $q->whereHas(
                        'usage',
                        fn($u) =>
                        $u->where('period_id', static::$activePeriodId)
                    ),
            ], 'qty')
        ;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
            'history' => Pages\ItemHistoryPage::route('/{record}/history'),
        ];
    }
}