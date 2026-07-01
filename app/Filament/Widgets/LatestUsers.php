<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestUsers extends BaseWidget
{

    public static function getHeading(): string
    {
        return 'User Terbaru';
    }

    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;


    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        $query = User::query()
            ->latest()
            ->limit(5);

        if ($user && $user->role === 'admin') {
            $query->where('role', '!=', 'super_admin');
        }

        return $query;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'super_admin']);
    }

    protected function getTableColumns(): array
    {

        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->icon('heroicon-o-user')
                ->color('primary'),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->icon('heroicon-o-envelope')
                ->color('info')
            ,

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat pada')
                ->date(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
