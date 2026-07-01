<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemTypeForm{
    public static function configure(Schema $schema)
    {
        return $schema
        ->schema([
            TextInput::make('name')
                ->required()
                ->columnSpanFull()
        ]);
    }
}
