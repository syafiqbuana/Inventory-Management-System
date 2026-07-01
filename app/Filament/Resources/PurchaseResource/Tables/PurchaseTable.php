<?php

use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PurchaseTable{

    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Pengadaan')
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['purchaseItems.item', 'createdBy']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('note')
                    ->label('Catatan')
                    ->alignLeft()
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('purchaseItems.item.name')
                    ->label('Items Dibeli')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->wrap()
                    ->limitList(2)
                    ->expandableLimitedList(),

                TextColumn::make('purchaseItems.unit_price')
                    ->label('Harga Unit')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->listWithLineBreaks()
                    ->alignCenter()
                    ->limitList(3)
                    ->expandableLimitedList(),

                TextColumn::make('purchaseItems.supplier')
                    ->label('Supplier')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),

                TextColumn::make('period.year')
                    ->label('Periode')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Tgl. Mutasi')
                    ->date('d/m/Y')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Filter Rentang Tanggal'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->color('info'),
                EditAction::make()
                    ->disabled(fn(Purchase $record) => $record->period->is_closed),
                DeleteAction::make()
                    ->disabled(fn(Purchase $record) => $record->period->is_closed)
                    ->color('danger'),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Buat Pengadan Baru')
                    ->icon('heroicon-m-plus'),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->action(function ($livewire) {
                    
                        $ids = $livewire->getFilteredTableQuery()->pluck('id')->toArray();

                        $url = route('purchase.report.stream', ['ids' => implode(',', $ids)]);

                        
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ]);
    }
}