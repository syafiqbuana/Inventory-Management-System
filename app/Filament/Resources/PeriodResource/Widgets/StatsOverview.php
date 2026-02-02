<?php

namespace App\Filament\Resources\PeriodResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Period;

class StatsOverview extends BaseWidget
{
protected function getStats(): array
{
    $period = Period::where('is_closed', false)->first();

    if (! $period) {
        return [
            Stat::make('Periode Aktif', 'Tidak ada')
                ->description('Belum ada periode yang aktif')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }

    return [
        Stat::make('Periode Aktif', $period->year)
            ->description('Sedang berjalan')
            ->icon('heroicon-o-check-circle')
            ->color('success'),

        Stat::make('Status', 'Aktif')
            ->icon('heroicon-o-lock-open')
            ->color('success'),
    ];
}

}

