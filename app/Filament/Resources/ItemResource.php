<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\App;
use Filament\Tables\Actions\Action;
use App\Models\Category;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
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
                Forms\Components\Grid::make()
                    ->schema([
                        // Form untuk CREATE (menambah multiple items sekaligus)
                        Repeater::make('new_items') 
                            ->label('Tambah Item') 
                            ->reactive()
                            ->default([])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Barang Lainnya')
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(Category::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('initial_stock')
                                    ->label('Stok Awal')
                                    ->required()
                                    ->default(0)
                                    ->numeric()
                            ])
                            ->columnSpanFull()
                            ->visible(fn ($livewire) => $livewire instanceof Pages\CreateItem),

                        // Form untuk EDIT (edit single item)
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn ($livewire) => $livewire instanceof Pages\EditItem),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn ($livewire) => $livewire instanceof Pages\EditItem),
                            
                        Forms\Components\TextInput::make('initial_stock')
                            ->label('Stok Awal')
                            ->required()
                            ->numeric()
                            ->visible(fn ($livewire) => $livewire instanceof Pages\EditItem),

                        Forms\Components\TextInput::make('total_stock')
                            ->label('Total Stok Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($livewire) => $livewire instanceof Pages\EditItem),
                    ]),                            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('initial_stock')->label('Initial Stock')->alignCenter(),
                Tables\Columns\TextColumn::make('total_stock')->label('Total Stock')->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->alignCenter(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('View Details')
                    ->url(fn(Item $record) => PurchaseResource::getUrl('index', ['filters' => ['purchaseItems.item_id' => $record->id]])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])

            ->headerActions([
                Action::make('export_pdf')
                    ->label('Cetak Laporan PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $query = $livewire->getFilteredTableQuery();
                        $appliedFilters = $livewire->tableFilters ?? [];
                        $keteranganFilter = [];
                        $records = $query->with('category')->get();

                        if (isset($appliedFilters['category']['value']) && $appliedFilters['category']['value'] !== null) {
                            $categoryId = $appliedFilters['category']['value'];
                            $categoryName = Category::find($categoryId)->name ?? 'Tidak Ditemukan';
                            $keteranganFilter[] = 'Filter Kategori: ' . $categoryName;
                        }

                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];

                            if ($dateRangeString) {
                                $dates = explode(' - ', $dateRangeString);
                                if (count($dates) === 2) {
                                    $start = trim($dates[0]);
                                    $end = trim($dates[1]);

                                    $formattedStart = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->translatedFormat('d F Y');
                                    $formattedEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->translatedFormat('d F Y');

                                    $keteranganFilter[] = 'Filter Tanggal Dibuat: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
                                }
                            }
                        }

                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadView('pdf.item_report', compact('records', 'keteranganFilter'));

                        $fileName = 'Laporan_Data_Barang_' . now()->format('Ymd_His') . '.pdf';

                        return response()->streamDownload(function () use ($pdf, $fileName) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),
            ]);

    }

    /**
     * Returns the relations available for the resource.
     *
     * @return array<string>
     */
    public static function getRelations(): array
    {
        // The relations available for the resource.
        return [
            
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