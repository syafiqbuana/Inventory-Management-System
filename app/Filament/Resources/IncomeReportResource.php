<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeReportResource\Pages;
use App\Filament\Resources\IncomeReportResource\RelationManagers;
use App\Models\IncomeReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomeReportResource extends Resource
{
    protected static ?string $model = IncomeReport::class;

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
            'index' => Pages\ListIncomeReports::route('/'),
            'create' => Pages\CreateIncomeReport::route('/create'),
            'edit' => Pages\EditIncomeReport::route('/{record}/edit'),
        ];
    }
}
