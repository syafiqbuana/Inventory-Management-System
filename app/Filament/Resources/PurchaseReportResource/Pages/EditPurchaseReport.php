<?php

namespace App\Filament\Resources\PurchaseReportResource\Pages;

use App\Filament\Resources\PurchaseReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseReport extends EditRecord
{
    protected static string $resource = PurchaseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
