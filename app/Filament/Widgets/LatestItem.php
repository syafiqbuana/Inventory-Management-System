<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Period;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;


class LatestItem extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?int $activePeriodId = null;
    protected int|string|array $columnSpan = 'full';

        public static function boot(): void
    {
        static::$activePeriodId = Period::active()->id;
    }

    public function table(Table $table): Table
    {
        return $table
        ->heading('Item Terbaru')
        ->query(
            Item::query()->with('category')->orderByDesc('created_at')->limit(5))
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Item'),

            Tables\Columns\TextColumn::make('category.name')
                ->label('Kategori')
                ->badge()
                ->color('info'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat pada')
                ->date(),
                            Tables\Columns\TextColumn::make('initial_stock')->label('Stok Awal')->alignCenter()
                    //jika initial stock nya 0 maka tampilkan -
                    ->formatStateUsing(fn($state) => $state == 0 ? '-' : $state),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Total Stock')
                    ->alignCenter()
                    ->state(
                        fn(Item $record) =>
                        $record->stockForPeriod(static::$activePeriodId)
                    ),
                Tables\Columns\TextColumn::make('display_price')
                    ->label('Harga')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        return $record->price != 0
                            ? $record->price
                            : $record->purchaseItems
                                ->sortByDesc('created_at')
                                ->first()
                                    ?->unit_price ?? 0;
                    })
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format((int) $state, 0, ',', '.')
                    )
                ,
                //show item type
                Tables\Columns\TextColumn::make('itemType.name')->label('Satuan')->alignCenter(),
                Tables\Columns\TextColumn::make('initialPeriod.year')->label('Periode')->alignCenter(),
        ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
