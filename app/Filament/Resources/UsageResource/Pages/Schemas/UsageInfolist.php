<?php

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UsageInfolist{
    public static function configure(Schema $schema){ {
        return $schema
        ->schema([
                Section::make('Informasi Penggunaan')
                    ->schema([
                        TextEntry::make('used_by')
                            ->label('Diambil Oleh :'),

                        TextEntry::make('usage_date')
                            ->label('Tanggal Penggunaan :')
                            ->date('d F Y'),

                        TextEntry::make('used_for')
                            ->label('Digunakan Untuk :'),

                        TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh :')
                            ->default('N/A')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),

                Section::make('Detail Item')
                    ->schema([
                        RepeatableEntry::make('usageItems')
                            ->label('Item :')
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
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
    }
}