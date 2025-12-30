<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Income;

class EditBalance extends EditRecord
{
    protected static string $resource = BalanceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalIncome = Income::sum('amount');
        $data['amount'] = $data['amount'] + $totalIncome;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
