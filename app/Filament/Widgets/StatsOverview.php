<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Period;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $stat = [];
        $activePeriod = Period::where('is_closed', false)->first();

        $stat[] = Stat::make(
            'Periode',
            $activePeriod?->year ?? 'Tidak ada'
        )
            ->color('success')
            ->icon('heroicon-o-calendar-days')
            ->description('Periode Aktif');

        $stat[] = Stat::make('Jenis Item', Item::count())
            ->color('primary')
            ->icon('heroicon-o-archive-box')
            ->description('Jenis Item');

        $stat[] = Stat::make('Kategori', Category::count())
            ->color('warning')
            ->icon('heroicon-o-list-bullet')
            ->description('Kategori');

        $stat[] = Stat::make('Role Anda', Str::title($user->role))
            ->color('info')
            ->icon('heroicon-o-user')
            ->description('Role Anda');

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            $stat[] = Stat::make('User', User::count())
                ->color('success')
                ->icon('heroicon-o-users')
                ->description('Jumlah User');
        }
        return $stat;
    }
}
