<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodResource\Pages;
use App\Models\Period;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;



class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    public static function getPluralLabel(): string
    {
        return 'Periode';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function form(Schema $form): Schema
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
                    Action::make('close')
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
