<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $stat = []; 

        $stat[] = Stat::make('Jenis Item', Item::count())
            ->color('primary')
            ->icon('heroicon-o-archive-box')
            ->description('Jenis Item');

        $stat[] = Stat::make('Kategori', Category::count())
            ->color('warning')
            ->icon('heroicon-o-list-bullet')
            ->description('Kategori');

        $stat[] = Stat::make('Role Anda',Str::title($user->role))
            ->color('info')
            ->icon('heroicon-o-user')
            ->description('Role Anda');

        if ($user->role === 'admin') {
            $stat[] = Stat::make('User', User::count())
                ->color('success')
                ->icon('heroicon-o-users')
                ->description('Jumlah User');
        }
        return $stat;
    }
}
