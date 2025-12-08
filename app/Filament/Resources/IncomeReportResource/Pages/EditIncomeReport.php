<?php

namespace App\Filament\Resources\IncomeReportResource\Pages;

use App\Filament\Resources\IncomeReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomeReport extends EditRecord
{
    protected static string $resource = IncomeReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
