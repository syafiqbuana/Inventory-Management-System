<?php

use App\Models\Usage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Actions\Action;

class UsageTable{

    public static function configure(Table $table)
    {
        return $table
        ->columns([
            TextColumn::make('used_by')
                    ->label('Diambil Oleh')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->iconColor('primary'),

            TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->iconColor('success'),

            TextColumn::make('used_for')
                    ->label('Digunakan Untuk')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 40) {
                            return $state;
                        }
                        return null;
                    }),

                //menampilkan list item yang digunakan menggunakan list
            TextColumn::make('usageItems.item.name')
                    ->label('Item')
                    ->getStateUsing(function (Usage $record): array {
                        return $record->usageItems->map(fn($item) => $item->item->name ?? 'Item Tidak Dikenal')->toArray()
                        ;
                    })
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->listWithLineBreaks()
                    ->bulleted(),

            TextColumn::make('total_qty')
                    ->label('Total Barang')
                    ->getStateUsing(function (Usage $record): int {
                        return $record->usageItems->sum('qty');
                    })
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
            TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->badge()
                    ->color('primary'),
            TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('createdBy')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('usage_date', 'desc')
            ->filters([
                // Filter Kategori
                SelectFilter::make('category')
                    ->label('Kategori Item')
                    ->relationship('usageItems.item.category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Filter Pengguna
                SelectFilter::make('used_by')
                    ->label('Pengguna')
                    ->options(function () {
                        return Usage::query()
                            ->distinct()
                            ->pluck('used_by', 'used_by')
                            ->toArray();
                    })
                    ->searchable(),

                // Filter Rentang Tanggal
                DateRangeFilter::make('usage_date')
                    ->label('Rentang Tanggal Penggunaan'),
        ])
                    ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Buat Penggunaan Baru')
                    ->icon('heroicon-m-plus'),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Ekspor ke PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->url(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $appliedFilters = $livewire->tableFilters ?? [];

                        $params = [];
                        if (isset($appliedFilters['category']['values']) && !empty($appliedFilters['category']['values'])) {
                            $params['categories'] = $appliedFilters['category']['values'];
                        }

                        
                        if (isset($appliedFilters['used_by']['value']) && $appliedFilters['used_by']['value'] !== null) {
                            $params['used_by'] = $appliedFilters['used_by']['value'];
                        }

                    
                        if (isset($appliedFilters['usage_date']['usage_date'])) {
                            $dateRangeString = $appliedFilters['usage_date']['usage_date'];
                            if ($dateRangeString) {
                                $params['date_range'] = $dateRangeString;
                            }
                        }

                        return route('usage.report.export', $params);
                    })
                    ->openUrlInNewTab(),
            ]);
    }
}