<?php

namespace App\Filament\Resources\UsageReportResource\Pages;

use App\Filament\Resources\UsageReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsageReports extends ListRecords
{
    protected static string $resource = UsageReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
