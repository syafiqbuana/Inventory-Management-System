<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageReportResource\Pages;
use App\Filament\Resources\UsageReportResource\RelationManagers;
use App\Models\UsageReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsageReportResource extends Resource
{
    protected static ?string $model = UsageReport::class;
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsageReports::route('/'),
            'create' => Pages\CreateUsageReport::route('/create'),
            'edit' => Pages\EditUsageReport::route('/{record}/edit'),
        ];
    }
}
