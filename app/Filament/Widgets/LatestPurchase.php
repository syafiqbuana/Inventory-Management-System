<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Tables;
use App\Filament\Resources\PurchaseResource\Pages\ViewPurchase;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPurchase extends BaseWidget
{
    protected static ?string $heading = 'Pembelian Terbaru';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Purchase::query()
                    ->with(['createdBy', 'period'])
                    ->latest('purchase_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('period.year')
                    ->label('Periode')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->color('info')
                     ->url(fn ($record) => ViewPurchase::getUrl(['record' => $record->id])),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn(Purchase $record) => $record->period->is_closed)
            ])
            ->paginated(false);
    }
}
