<?php

namespace App\Filament\Resources\ItemResource\Pages\Tables;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\PurchaseResource;
use App\Models\Item;
use App\Models\Period;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ItemTable
{


    public static function configure(Table $table)
    {
        $activePeriodId = Period::active()?->id;
        
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Barang')->alignCenter()->searchable(),
                TextColumn::make('category.name')->label('Kategori')->alignCenter(),
                TextColumn::make('initial_stock')->label('Stok Awal')->alignCenter()
                    ->formatStateUsing(fn($state) => $state == 0 ? '-' : $state),
                TextColumn::make('stock')
                    ->label('Total Stock')
                    ->alignCenter()
                    ->badge()
                    ->icon('heroicon-o-cube')
                    ->state(
                       fn(Item $record) => $record->stockForPeriod($activePeriodId)
                    )
                    ->color(fn($state) => $state < 10 ? 'danger' : 'success'),

                TextColumn::make('display_price')
                    ->label('Harga')
                    ->badge()
                    ->icon('heroicon-o-banknotes')
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
                TextColumn::make('itemType.name')->label('Satuan')->alignCenter(),
                TextColumn::make('initialPeriod.year')->label('Periode')->alignCenter(),
                TextColumn::make('createdBy.name')->label('Dibuat Oleh')->alignCenter()
                    ->badge()
                    ->icon('heroicon-o-user')
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                DateRangeFilter::make('created_at')
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make()
                        ->label('View Details')
                        ->url(fn(Item $record) => PurchaseResource::getUrl('index', ['filters' => ['purchaseItems.item_id' => $record->id]]))
                    ,
                    DeleteAction::make(),
                    Action::make('history')
                        ->label('Riwayat')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->url(fn(Item $record) => ItemResource::getUrl('history', ['record' => $record]))
                ])->iconButton()
                    ->link()
                    ->color('info')
                    ->label('Aksi')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])


            ->headerActions([
                Action::make('export_pdf')
                    ->label('Cetak Laporan PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->url(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $appliedFilters = $livewire->tableFilters ?? [];

                        $params = [];

                        if (isset($appliedFilters['category']['value']) && $appliedFilters['category']['value'] !== null) {
                            $params['category'] = $appliedFilters['category']['value'];
                        }

                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];
                            if ($dateRangeString) {
                                $params['date_range'] = $dateRangeString;
                            }
                        }

                        return route('item.report.export', $params);
                    })
                    ->openUrlInNewTab()
                    ->requiresConfirmation()
                    ->modalHeading('Cetak Laporan PDF')
                    ->modalDescription('Laporan akan dibuka di tab baru. Pastikan browser Anda tidak memblokir pop-up.')
                    ->modalSubmitActionLabel('Lanjutkan'),
            ]);
    }
}