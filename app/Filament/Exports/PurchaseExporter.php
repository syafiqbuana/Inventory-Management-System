<?php

namespace App\Filament\Exports;

use App\Models\Purchase;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PurchaseExporter extends Exporter
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            
            // Kolom Nama Item
            ExportColumn::make('item_names')
                ->label('Items Dibeli')
                // Ganti using() dengan formatStateUsing()
                ->formatStateUsing(function ($state, Purchase $record): string {
                    // Mengambil data dari relasi purchaseItems dan menggabungkannya
                    return $record->purchaseItems->map(fn ($item) => $item->item->name ?? 'Item Tidak Dikenal')->implode(' | ');
                }),

            // Kolom Kuantitas
            ExportColumn::make('item_quantities')
                ->label('Jumlah Dibeli')
                // Ganti using() dengan formatStateUsing()
                ->formatStateUsing(function ($state, Purchase $record): string {
                    // Menggabungkan kuantitas item
                    return $record->purchaseItems->map(fn ($item) => $item->qty ?? 0)->implode(' | ');
                }),
                
            // Kolom Harga Satuan
            ExportColumn::make('unit_prices')
                ->label('Harga Satuan')
                // Ganti using() dengan formatStateUsing()
                ->formatStateUsing(function ($state, Purchase $record): string {
                    // Menggabungkan harga unit item
                    return $record->purchaseItems->map(function ($item) {
                        $unitPrice = $item->unit_price ?? 0;
                        return 'IDR ' . number_format($unitPrice, 0, ',', '.');
                    })->implode(' | ');
                }),

            // Kolom Subtotal Item
            ExportColumn::make('item_subtotals')
                ->label('Subtotal Item')
                // Tambahkan formatStateUsing()
                ->formatStateUsing(function ($state, Purchase $record): string {
                    return $record->purchaseItems->map(function ($item) {
                        $subtotal = $item->subtotal ?? 0;
                        return 'IDR ' . number_format($subtotal, 0, ',', '.');
                    })->implode(' | ');
                }),
            
            // Kolom total_amount yang merupakan kolom fisik di tabel 'purchases'
            ExportColumn::make('total_amount')->label('Total Amount'),

            ExportColumn::make('note'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your purchase export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}