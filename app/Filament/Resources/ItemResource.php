<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use Filament\Resources\Resource;
use App\Models\ItemType;
use App\Models\Period;
use Filament\Tables;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;


class ItemResource extends Resource
{
    protected static ?string $model = Item::class;


    protected static ?string $navigationLabel = 'Stok Awal & Data Barang';

    protected static ?string $pluralModelLabel = 'Stok Awal & Data Barang';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_by_name')
                            ->label('Dibuat oleh :')
                            ->content(fn($record) => $record?->createdBy?->name ?? Auth::user()->name),
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
                                    ->minValue(1)
                                    ->numeric(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->minValue(1)
                                    ->numeric(),
                                Forms\Components\Select::make('item_type_id')
                                    ->label('Satuan Barang')
                                    ->options(ItemType::pluck('name', 'id'))
                                    ->searchable()
                            ])
                            ->columnSpanFull()
                            ->visible(fn($livewire) => $livewire instanceof Pages\CreateItem),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        Forms\Components\TextInput::make('initial_stock')
                            ->label('Stok Awal')
                            ->required()
                            ->numeric()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),

                        Forms\Components\TextInput::make('total_stock')
                            ->label('Total Stok Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        Forms\Components\Select::make('item_type_id')
                            ->label('Satuan')
                            ->options(ItemType::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn($livewire) => $livewire instanceof Pages\EditItem),
                        Forms\Components\Hidden::make('created_by')
                            ->default(Auth::user()->id)
                            ->dehydrated(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([
                10,
                25,
                50,
                100
            ])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Tidak Ada Barang')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Barang')->alignCenter()->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->alignCenter(),
                Tables\Columns\TextColumn::make('initial_stock')->label('Stok Awal')->alignCenter()
                    ->formatStateUsing(fn($state) => $state == 0 ? '-' : $state),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Total Stock')
                    ->alignCenter()
                    ->badge()
                    ->icon('heroicon-o-cube')
                    ->state(
                        fn(Item $record) =>
                        $record->stockForPeriod(static::$activePeriodId)
                    )
                    ->color(fn($state) => $state < 10 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('display_price')
                    ->label('Harga')
                    ->badge()
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        return $record->price != 0
                            ? $record->price
                            : $record->purchaseItems
                                ->sortByDesc('created_at')
                                ->first()
                                    ?->unit_price ?? 0;
                    })
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format((int) $state, 0, ',', '.')
                    )
                ,
                Tables\Columns\TextColumn::make('itemType.name')->label('Satuan')->alignCenter(),
                Tables\Columns\TextColumn::make('initialPeriod.year')->label('Periode')->alignCenter(),
                Tables\Columns\TextColumn::make('createdBy.name')->label('Dibuat Oleh')->alignCenter()
                    ->badge()
                    ->icon('heroicon-o-user')
                    ->color('primary'),

            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
            ])
            ->actions([
                Tables\actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make()
                        ->label('View Details')
                        ->url(fn(Item $record) => PurchaseResource::getUrl('index', ['filters' => ['purchaseItems.item_id' => $record->id]]))
                    ,
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('history')
                        ->label('Riwayat')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->url(fn(Item $record) => ItemResource::getUrl('history', ['record' => $record]))
                ])->iconButton()
                    ->link()
                    ->color('info')
                    ->label('Aksi')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])


            ->headerActions([
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(
                            new \App\Exports\ItemTemplateExport(),
                            'template_import_barang_' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),
                Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ])
                            ->required()
                            ->maxSize(5120)
                            ->storeFiles(false)
                            ->helperText('Format: .xls atau .xlsx, Maksimal 5MB')
                    ])
                    ->action(function (array $data) {
                        try {
                            $file = $data['file'];
                            $import = new ItemImport();
                            Excel::import($import, $file);

                            $summary = $import->getSummary();
                            $failures = $import->getFailures();

                            if (count($failures) > 0) {
                                $errorMessages = [];
                                foreach ($failures as $failure) {
                                    $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                                }
                                Notification::make()
                                    ->title('Import Gagal Sebagian')
                                    ->body("✅ Berhasil: {$summary['processed']} data\n❌ Gagal: " . count($failures) . " baris\n\n" . implode("\n", array_slice($errorMessages, 0, 3)))
                                    ->warning()
                                    ->duration(10000)
                                    ->send();
                            } else {
                                Log::info('===== SELESAI IMPORT (SUKSES) =====');

                                Notification::make()
                                    ->title('Import Berhasil!')
                                    ->body("Berhasil mengimport {$summary['processed']} data barang ")
                                    ->success()
                                    ->send();
                            }

                        } catch (\Exception $e) {
                            Log::error('===== IMPORT ERROR =====', [
                                'message' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Import Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalHeading('Import Data Barang')
                    ->modalDescription('Upload file Excel dengan format sesuai template')
                    ->modalSubmitActionLabel('Import')
                    ->modalWidth('md'),
                Action::make('export_pdf')
                    ->label('Cetak Laporan PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->url(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $appliedFilters = $livewire->tableFilters ?? [];

                        $params = [];

                        if (isset($appliedFilters['category']['value']) && $appliedFilters['category']['value'] !== null) {
                            $params['category'] = $appliedFilters['category']['value'];
                        }

                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];
                            if ($dateRangeString) {
                                $params['date_range'] = $dateRangeString;
                            }
                        }

                        return route('item.report.export', $params);
                    })
                    ->openUrlInNewTab()
                    ->requiresConfirmation()
                    ->modalHeading('Cetak Laporan PDF')
                    ->modalDescription('Laporan akan dibuka di tab baru. Pastikan browser Anda tidak memblokir pop-up.')
                    ->modalSubmitActionLabel('Lanjutkan'),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?int $activePeriodId = null;

    public static function getEloquentQuery(): Builder
    {
        if (static::$activePeriodId === null) {
            static::$activePeriodId = Period::query()
                ->where('is_closed', false)
                ->value('id');
        }

        return parent::getEloquentQuery()
            ->with(['category', 'createdBy', 'itemType'])
            ->withSum([
                'purchaseItems as purchased_qty' => fn($q) =>
                    $q->whereHas(
                        'purchase',
                        fn($p) =>
                        $p->where('period_id', static::$activePeriodId)
                    ),
            ], 'qty')
            ->withSum([
                'usageItems as used_qty' => fn($q) =>
                    $q->whereHas(
                        'usage',
                        fn($u) =>
                        $u->where('period_id', static::$activePeriodId)
                    ),
            ], 'qty')
        ;
    }

    public static function boot(): void
    {
        static::$activePeriodId = Period::active()->id;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
            'history' => Pages\ItemHistoryPage::route('/{record}/history'),
        ];
    }
}