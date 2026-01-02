<?php

namespace App\Filament\Resources\IncomeResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Income;
use App\Models\Balance;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Income',
                'Rp ' . number_format(Income::sum('amount'), 0, ',', '.')
            )
                ->description('Total seluruh pemasukan')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Balance::count() > 0 ? Stat::make('Saldo Keseluruhan', 'Rp ' . number_format(Balance::first()->amount, 0, ',', '.'))
                ->color('primary')
                ->icon('heroicon-s-banknotes')
                ->description('Total seluruh saldo') : null
                ,
            Stat::make('Pemasukan hari ini', 'Rp ' . number_format(Income::whereDate('created_at', today())->sum('amount'), 0, ',', '.')
            )
                ->description('Pemasukan hari ini')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
