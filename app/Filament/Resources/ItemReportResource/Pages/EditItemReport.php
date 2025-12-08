<?php

namespace App\Filament\Resources\ItemReportResource\Pages;

use App\Filament\Resources\ItemReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemReport extends EditRecord
{
    protected static string $resource = ItemReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
