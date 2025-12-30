<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $itemsToCreate = $data['new_items'] ?? [];
        if (empty($itemsToCreate)) {
            
            $this->halt();
        }

        $lastCreatedItem = null;
        foreach ($itemsToCreate as $itemData) {
            $lastCreatedItem = Item::create($itemData); 
        }

        return $lastCreatedItem; 
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        $count = count($this->data['new_items'] ?? []);
        return $count > 0 ? "{$count} Items created successfully!" : "Item creation successful.";
    }
}