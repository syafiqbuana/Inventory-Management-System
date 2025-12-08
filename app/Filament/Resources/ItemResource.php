<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Models\Category;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('stock')->Label('Stock')->getStateUsing(fn($record) => $record->PurchaseItems()->sum('qty') - $record->usages()->sum('qty'))->alignCenter()->badge()->color('success'),
                Tables\Columns\TextColumn::make('latest_price')->label('Latest Price')->money('idr', true)->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')
                    ->label('Filter by Category')
                    ->options([
                        Category::all()->pluck('name', 'id')
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View Details')
                    ->url(fn(Item $record) => PurchaseResource::getUrl('index', ['filters' => ['purchaseItems.item_id' => $record->id]])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
