<?php

namespace App\Filament\Resources\ItemResource\Pages\Schemas;

use App\Models\Category;
use App\Models\ItemType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(
            [
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('created_by_name')
                            ->label('Dibuat oleh :')
                            ->content(fn($record) => $record?->createdBy?->name ?? Auth::user()->name),
                            Grid::make(2)
                                ->schema([
                                    Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(Category::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('name')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('initial_stock')
                                    ->label('Stok Awal')
                                    ->required()
                                    ->minValue(1)
                                    ->numeric(),
                                TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->minValue(1)
                                    ->numeric(),
                                Select::make('item_type_id')
                                    ->label('Satuan Barang')
                                    ->options(ItemType::pluck('name', 'id'))
                                    ->searchable(),
                                ]),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        TextInput::make('name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        TextInput::make('initial_stock')
                            ->label('Stok Awal')
                            ->required()
                            ->numeric()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        TextInput::make('total_stock')
                            ->label('Total Stok Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        Select::make('item_type_id')
                            ->label('Satuan')
                            ->options(ItemType::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        Hidden::make('created_by')
                            ->default(Auth::user()->id)
                            ->dehydrated(true),
                    ])
            ]
        )
        ;
    }

}