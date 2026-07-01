<?php

use Filament\Schemas\Schema;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;

class PurchaseInfolist{

    public static function configure(Schema $schema){
        return $schema
            ->schema([
                Section::make('Informasi Pembelian')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('note')
                                    ->label('Catatan'),
                                TextEntry::make('purchase_date')
                                    ->label('Tanggal Pembelian')
                                    ->date('d F Y')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('total_amount')
                                    ->label('Total Pembelian')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('period.year')
                                    ->label('Periode')
                                    ->badge()
                                    ->color('success'),

                            ]),

                        TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh')
                            ->default('N/A')
                            ->badge()
                            ->color('primary'),

                        Section::make('Item :')
                            ->schema([
                                RepeatableEntry::make('purchaseItems')
                                    ->label('Item')
                                    ->table([
                                        TableColumn::make('Nama Item'),
                                        TableColumn::make('Kategori'),
                                        TableColumn::make('Jumlah'),
                                        TableColumn::make('Harga'),
                                    ])
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
                                    ]),
                            ]),
                    ])

            ]);
    }
}