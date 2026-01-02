<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchase;
use App\Models\Balance;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pembelian', 'Rp ' . number_format(Purchase::sum('total_amount'), 0, ',', '.'))
                ->description('Total seluruh pembelian')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
            Balance::count() > 0 ? Stat::make('Saldo Keseluruhan', 'Rp ' . number_format(Balance::first()->amount, 0, ',', '.'))
                ->color('primary')
                ->icon('heroicon-s-banknotes')
                ->description('Total seluruh saldo') : null
                ,
            Stat::make('Transaksi hari ini', Purchase::whereDate('created_at', today())->count())
                ->description('Transaksi hari ini')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
                
        ];
    }
}
