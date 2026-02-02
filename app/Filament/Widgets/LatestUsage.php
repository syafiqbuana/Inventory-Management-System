<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Usage;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\UsageResource\Pages\ViewUsage;


class LatestUsage extends BaseWidget
{

    protected static ?string $heading = 'Penggunaan Terbaru';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Usage::query()
                    ->with(['createdBy', 'period'])
                    ->latest('usage_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('used_for')
                    ->label('Digunakan Untuk'),
                Tables\Columns\TextColumn::make('used_by')
                    ->label('Penerima'),
                Tables\Columns\TextColumn::make('period.year')
                    ->label('Periode')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->badge()
                    ->color('info'),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->color('info')
                ->url(fn ($record) => ViewUsage::getUrl(['record' => $record->id])),
            ]);
    }

}
