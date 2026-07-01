<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tables\Actions\EditAction;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }
    public static function getPluralModelLabel(): string
    {
        return 'Kategori Barang';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')->label('Jumlah Item')->alignCenter()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->alignCenter()
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Buat Kategori Baru')
                    ->icon('heroicon-m-plus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->withCount('items');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
        ];
    }
}
