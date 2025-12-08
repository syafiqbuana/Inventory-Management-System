<?php

namespace App\Filament\Resources\PurchaseReportResource\Pages;

use App\Filament\Resources\PurchaseReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseReports extends ListRecords
{
    protected static string $resource = PurchaseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
