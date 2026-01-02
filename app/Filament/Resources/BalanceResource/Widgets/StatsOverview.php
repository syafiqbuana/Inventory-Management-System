<?php

namespace App\Filament\Resources\BalanceResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Balance;
use League\CommonMark\Extension\DescriptionList\Node\Description;
use App\Models\Income;
use App\Models\Purchase;
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //ketika belum ada data di tabel balance,maka jangan tampilkan Stat
            Balance::count() > 0 ? Stat::make('Saldo Keseluruhan', 'Rp ' . number_format(Balance::first()->amount, 0, ',', '.'))
                ->color('primary')
                ->icon('heroicon-s-banknotes')
                ->description('Total seluruh saldo') : null
                ,
            Stat::make('Pemasukan Keseluruhan', 'Rp ' . number_format(Income::sum('amount'), 0, ',', '.'))->icon('heroicon-s-banknotes')            
                ->color('success')
                ->description('Total seluruh pemasukan'),
            Stat::make('Pengeluaran Keseluruhan', 'Rp ' . number_format(Purchase::sum('total_amount'), 0, ',', '.'))
                ->color('danger')
                ->icon('heroicon-s-banknotes')
                ->description('Total seluruh pengeluaran'),
        ];
    }
}
