<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TableEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Usage;

class ViewUsage extends ViewRecord
{
    protected static string $resource = UsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn(Usage $record) => $record->period->is_closed),

            Actions\Action::make('nota_permohonan')
                ->label('Cetak')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn() => route('usage.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
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