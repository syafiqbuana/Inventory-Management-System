<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodResource\Pages;
use App\Filament\Resources\PeriodResource\RelationManagers;
use App\Models\Period;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    protected static ?string $pluralLabel = 'Periode';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('year')->label('Periode')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_closed')->boolean()->label('Tutup')->sortable(),
                Tables\Columns\TextColumn::make('closed_at')->label('Tutup Pada')->date()->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                                Tables\Actions\Action::make('close')
                    ->label('Tutup Periode')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Period $record) => ! $record->is_closed)
                    ->action(fn (Period $record) =>
                        app(\App\Services\ClosePeriodService::class)->close($record)
                    ),
                
            ])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListPeriods::route('/'),
        ];
    }
}
