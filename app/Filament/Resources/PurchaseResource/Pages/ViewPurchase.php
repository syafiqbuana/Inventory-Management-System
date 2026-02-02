<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn(Purchase $record) => $record->period?->is_closed),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pembelian')
                    ->schema([
                        TextEntry::make('note')
                            ->label('Catatan'),
                        TextEntry::make('purchase_date')
                            ->label('Tanggal Pembelian')
                            ->date('d F Y'),

                        TextEntry::make('total_amount')
                            ->label('Total Pembelian')
                            ->money('IDR'),

                        TextEntry::make('period.year')
                            ->label('Periode')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh')
                            ->default('N/A')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),

                Section::make('Detail Item Pembelian')
                    ->schema([
                        RepeatableEntry::make('purchaseItems')
                            ->label('Item')
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Nama Item'),

                                TextEntry::make('item.category.name')
                                    ->label('Kategori')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('qty')
                                    ->label('Jumlah')
                                    ->badge()
                                    ->color('danger'),

                                TextEntry::make('unit_price')
                                    ->label('Harga')
                                    ->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
