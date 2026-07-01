<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TableEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Usage;

class ViewUsage extends ViewRecord
{
    protected static string $resource = UsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn(Usage $record) => $record->period->is_closed),

            Actions\Action::make('nota_permohonan')
                ->label('Cetak')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn() => route('usage.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}