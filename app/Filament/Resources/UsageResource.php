<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageResource\Pages;
use App\Filament\Resources\UsageResource\RelationManagers;
use App\Models\Usage;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\App;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Symfony\Component\String\s;

class UsageResource extends Resource
{
    protected static ?string $model = Usage::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)->schema([
                    Repeater::make('usages')
                        ->label('Item yang Digunakan')
                        ->schema([
                            Forms\Components\Select::make('item_id')
                                ->label('Item')
                                ->relationship('item', 'name')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('qty')
                                ->label('Jumlah Digunakan')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->columnSpan(1),

                            TextInput::make('used_for')
                                ->label('Digunakan Untuk')
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columns(4)
                        ->columnSpanFull()
                        ->required()
                        ->default([['item_id' => null, 'qty' => 1, 'used_for' => '']]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item Digunakan')
                    ->searchable()
                    ->sortable(),
                
                // Kolom kategori baru
                Tables\Columns\TextColumn::make('item.category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah')
                    ->numeric()
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('used_for')
                    ->label('Digunakan Untuk')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl. Penggunaan')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // Filter Kategori
                SelectFilter::make('category')
                    ->label('Filter by Category')
                    ->relationship('item.category', 'name')
                    ->searchable()
                    ->preload(),
                
                // Filter Rentang Tanggal
                DateRangeFilter::make('created_at')
                    ->label('Filter Rentang Tanggal')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Ekspor ke PDF')
                    ->color('danger')
                    ->icon('heroicon-o-printer')
                    ->action(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $query = $livewire->getFilteredTableQuery();
                        
                        $appliedFilters = $livewire->tableFilters ?? [];
                        $keteranganFilter = [];
                        
                        // Load relasi item dan category
                        $records = $query->with(['item.category'])->get();
                        
                        // 1. Filter Kategori
                        if (isset($appliedFilters['category']['value']) && $appliedFilters['category']['value'] !== null) {
                            $categoryId = $appliedFilters['category']['value'];
                            $categoryName = Category::find($categoryId)->name ?? 'Tidak Ditemukan';
                            $keteranganFilter[] = 'Filter Kategori: ' . $categoryName;
                        }
                        
                        // 2. Filter Rentang Tanggal
                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];
                            
                            if ($dateRangeString) {
                                $dates = explode(' - ', $dateRangeString);
                                if (count($dates) === 2) {
                                    $start = trim($dates[0]);
                                    $end = trim($dates[1]);
                                    
                                    $formattedStart = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->translatedFormat('d F Y');
                                    $formattedEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->translatedFormat('d F Y');
                                    
                                    $keteranganFilter[] = 'Filter Tanggal Penggunaan: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
                                }
                            }
                        }
                        
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadView('pdf.usage_report', compact('records', 'keteranganFilter'));
                        
                        $fileName = 'Laporan_Penggunaan_Barang_' . now()->format('Ymd_His') . '.pdf';
                        
                        return response()->streamDownload(function () use ($pdf, $fileName) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),
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
            'index' => Pages\ListUsages::route('/'),
            'create' => Pages\CreateUsage::route('/create'),
            'edit' => Pages\EditUsage::route('/{record}/edit'),
        ];
    }
}