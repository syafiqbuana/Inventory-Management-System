<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Actions;
use App\Models\Balance;
use Filament\Resources\Pages\EditRecord;

class EditIncome extends EditRecord
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $balance = Balance::first();
                    if ($balance) {
                        $balance->decrement('amount', $this->record->amount);
                    }
                }),
        ];
    }
    protected float $oldAmount;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldAmount = $this->record->amount;

        return $data;
    }
    //update balance after edit income
    protected function afterSave()
    {
        $newAmount = $this->record->amount;
        $difference = $newAmount - $this->oldAmount;

        $balance = Balance::first();

        if ($balance) {
            $balance->increment('amount', $difference);
        }
    }

}
