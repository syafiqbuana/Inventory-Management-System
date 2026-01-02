<?php

namespace App\Filament\Resources\UsageResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Usage;
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

        $topItem = Usage::with('item')
            ->selectRaw('item_id, SUM(qty) as total_qty')
            ->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->first();
            
        return [
            Stat::make('Transaksi', Usage::count())
                ->color('primary')
                ->icon('heroicon-o-clipboard')
                ->description('Total seluruh transaksi'),
            Stat::make('Transaksi hari ini', Usage::whereDate('created_at', today())->count())
                ->description('Transaksi hari ini')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make(
                'Paling Sering Dipakai',
                $topItem
                ? $topItem->item->name
                : 'Belum ada data'
            )
                ->description(
                    $topItem
                    ? 'Total dipakai: ' . $topItem->total_qty
                    : 'Tidak ada penggunaan barang'
                )
                ->color($topItem ? 'primary' : 'gray')
                ->icon('heroicon-o-fire'),
        ];
    }
}
