<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Models\Item;
use App\Models\ItemType;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions;


class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Pengadaan Barang';

    protected static ?string $pluralModelLabel = 'Pengadaan Barang';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('PurchaseTabs')
                    ->columnSpanFull()
                    ->tabs([
                        // --- TAB 1 (Tetap Sama) ---
                        Forms\Components\Tabs\Tab::make('Pengadaan Barang')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Form Pengadaan Barang')
                                    ->description('Pengadaan barang dengan harga yang sama akan menambahkan stok barang, jika pengadaan barang memiliki harga yang berbeda dengan harga sebelumnya, silahkan menuju Pengadaan Barang Baru untuk menambahkan pengadaan barang baru')
                                    ->schema([

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                TextInput::make('note')
                                                    ->required()
                                                    ->label('Catatan')
                                                    ->columnSpan(2),

                                                DatePicker::make('purchase_date')
                                                    ->label('Tanggal Pembelian')
                                                    ->default(now())
                                                    ->required(),

                                                TextInput::make('total_amount')
                                                    ->label('Total Pengadaan')
                                                    ->prefix('Rp')
                                                    ->readonly()
                                                    ->numeric()
                                                    ->default(0)
                                                    ->dehydrated(),
                                            ]),

                                        Repeater::make('purchaseItems')
                                            ->relationship()
                                            ->live()
                                            ->schema([
                                                Select::make('item_id')
                                                    ->label('Nama Barang')
                                                    ->options(
                                                        Item::query()
                                                            ->where('price', '>', 0)
                                                            ->orderBy('name')
                                                            ->pluck('name', 'id')
                                                    )
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(
                                                        fn(Set $set, $state) =>
                                                        $set('unit_price', Item::find($state)?->price ?? 0)
                                                    ),
                                                TextInput::make('qty')
                                                    ->label('Jumlah')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(0)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(
                                                        fn(Get $get, Set $set)
                                                        => self::updateSubtotalAndTotal($get, $set)
                                                    ),

                                                TextInput::make('unit_price')
                                                    ->label('Harga Satuan')
                                                    ->numeric()
                                                    ->required()
                                                    ->readOnly()
                                                    ->prefix('Rp')
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(
                                                        fn(Get $get, Set $set)
                                                        => self::updateSubtotalAndTotal($get, $set)
                                                    ),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->readonly()
                                                    ->dehydrated()
                                                    ->prefix('Rp'),

                                                TextInput::make('supplier')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->afterStateUpdated(
                                                fn(Get $get, Set $set)
                                                => self::updateTotalAmount($get, $set)
                                            ),
                                        Actions::make([
                                            FormAction::make('savePurchaseItems')
                                                ->label('Simpan Pengadaan')
                                                ->color('primary')
                                                ->action('savePurchaseItems'),
                                        ])->visible(fn($livewire) => $livewire instanceof Pages\CreatePurchase),
                                    ]),
                            ]),


                        // --- TAB 2 (Solusi Baru dengan 2 Section) ---
                        Forms\Components\Tabs\Tab::make('Pengadaan Barang Baru')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                // SECTION 1: Registrasi Barang Baru
                                Section::make('Daftarkan Barang Baru')
                                    ->description('Daftarkan barang baru sebelum melakukan pengadaan barang')
                                    ->schema([
                                        Forms\Components\Grid::make(1)
                                            ->schema([
                                                TextInput::make('new_item_name')->label('Nama Barang Baru'),
                                                Select::make('new_item_category')->label('Kategori')->options(Category::pluck('name', 'id')),
                                                Select::make('new_item_type')->label('Satuan')->options(ItemType::pluck('name', 'id')),
                                            ]),
                                    ])
                                    ->headerActions([
                                        FormAction::make('saveNewItem')
                                            ->label('Daftarkan Barang Baru')
                                            ->color('success')
                                            ->icon('heroicon-m-check-circle')
                                            ->action(function (Get $get, Set $set) {
                                                if (!$get('new_item_name'))
                                                    return;

                                                Item::create([
                                                    'name' => $get('new_item_name'),
                                                    'category_id' => $get('new_item_category'),
                                                    'item_type_id' => $get('new_item_type'),
                                                    'price' => 0,
                                                    'stock' => 0,
                                                ]);

                                                Notification::make()->title('Barang Berhasil Didaftarkan!')->success()->send();

                                                // Reset form input barang baru
                                                $set('new_item_name', null);
                                                $set('new_item_price', null);
                                            }),
                                    ]),

                                // SECTION 2: Form Pengadaan (Sama dengan Tab 1)
                                Section::make('Pengadaan Barang baru')
                                    ->schema([
                                        TextInput::make('total_amount_tab2')
                                            ->readonly()
                                            ->label('Total Pengadaan')
                                            ->numeric()
                                            ->afterStateUpdated(fn(Get $get, Set $set)
                                                => self::updateSubtotalAndTotalTab2($get, $set))
                                            ->default(0)
                                            ->dehydrated(),
                                        TextInput::make('note_tab2')
                                            ->label('Catatan')
                                            ->maxLength(255)
                                            ->dehydrated(),

                                        Repeater::make('extraPurchaseItems')
                                            ->label('Item Tambahan')
                                            ->relationship('purchaseItems') // Menggunakan relasi yang sama agar tersimpan ke tabel yang sama
                                            ->live()
                                            ->schema([
                                                Select::make('item_id')
                                                    ->label('Pilih Barang yang Baru Dibuat')
                                                    ->relationship('item', 'name')
                                                    ->searchable()
                                                    ->getSearchResultsUsing(function (string $search) {
                                                        return Item::query()
                                                            ->where('name', 'like', "%{$search}%")
                                                            ->where(function ($query) {
                                                                $query->where('price', 0)
                                                                    ->orWhereNull('price');
                                                            })
                                                            // ✅ TIDAK perlu cek initial_stock
                                                            ->orderBy('id', 'desc')
                                                            ->limit(3)
                                                            ->get()
                                                            ->mapWithKeys(fn($item) => [
                                                                $item->id => sprintf(
                                                                    '%s | Satuan: %s',
                                                                    $item->name,
                                                                    $item->itemType?->name ?? '-'
                                                                )
                                                            ]);
                                                    })
                                                    ->getOptionLabelUsing(fn($value): ?string => Item::find($value)?->name)
                                                    ->live()
                                                    ->afterStateUpdated(fn(Set $set) => $set('unit_price', 0))
                                                    ->placeholder('Ketik nama barang baru...') // Gunakan live() sebagai pengganti reactive() di Filament V3
                                                ,
                                                TextInput::make('qty')
                                                    ->label('Jumlah')->numeric()->default(0)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateSubtotalAndTotalTab2($get, $set)),
                                                TextInput::make('unit_price')
                                                    ->label('Harga Satuan')->numeric()->prefix('Rp')
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateSubtotalAndTotalTab2($get, $set)),
                                                TextInput::make('subtotal')->label('Subtotal')->readonly()->dehydrated()->prefix('Rp'),
                                                TextInput::make('supplier')->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalAmountTab2($get, $set)),
                                        Actions::make([
                                            FormAction::make('saveNewPurchaseItems')
                                                ->label('Simpan Pengadaan')
                                                ->color('primary')
                                                ->action('saveNewPurchaseItems'),
                                        ])->visible(fn($livewire) => $livewire instanceof Pages\CreatePurchase),
                                    ])
                                    
                                    
                            ])->visible(fn($livewire) => $livewire instanceof Pages\CreatePurchase),
                    ]),
            ]);
    }


    // Fungsi Hitung Otomatis Subtotal + Update Total Besar
    protected static function updateSubtotalAndTotal(Get $get, Set $set): void
    {
        $qty = floatval($get('qty') ?? 0);
        $price = floatval($get('unit_price') ?? 0);

        $set('subtotal', $qty * $price);
        $items = $get('../../purchaseItems') ?? [];

        $total = 0;
        foreach ($items as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('../../total_amount', $total);
    }


    protected static function updateSubtotalAndTotalTab2(Get $get, Set $set): void
    {
        $qty = floatval($get('qty') ?? 0);
        $price = floatval($get('unit_price') ?? 0);

        $set('subtotal', $qty * $price);

        $items = $get('../../extraPurchaseItems') ?? [];

        $total = 0;
        foreach ($items as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('../../total_amount_tab2', $total);
    }


    protected static function updateTotalAmount(Get $get, Set $set): void
    {
        $items1 = $get('purchaseItems') ?? [];
        $items2 = $get('extraPurchaseItems') ?? [];

        $total = 0;
        foreach (array_merge($items1, $items2) as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('total_amount', $total);

    }

    protected static function updateTotalAmountTab2(Get $get, Set $set): void
    {
        $items = $get('extraPurchaseItems') ?? [];

        $total = 0;
        foreach ($items as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('total_amount_tab2', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['purchaseItems.item', 'createdBy']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->alignLeft()
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('purchaseItems.item.name')
                    ->label('Items Dibeli')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->wrap()
                    ->limitList(3)
                    ->expandableLimitedList(),

                Tables\Columns\TextColumn::make('purchaseItems.unit_price')
                    ->label('Harga Unit')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->listWithLineBreaks()
                    ->alignCenter()
                    ->limitList(3)
                    ->expandableLimitedList(),

                Tables\Columns\TextColumn::make('purchaseItems.supplier')
                    ->label('Supplier')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),

                Tables\Columns\TextColumn::make('period.year')
                    ->label('Periode')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl. Mutasi')
                    ->date('d/m/Y')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Filter Rentang Tanggal'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->color('info'),
                Tables\Actions\EditAction::make()
                    ->disabled(fn(Purchase $record) => $record->period->is_closed),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn(Purchase $record) => $record->period->is_closed)
                    ->color('danger'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Pengadan Baru')
                    ->icon('heroicon-m-plus'),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->action(function ($livewire) {
                        // Ambil ID dari data yang sudah terfilter di tabel
                        $ids = $livewire->getFilteredTableQuery()->pluck('id')->toArray();

                        $url = route('purchase.report.stream', ['ids' => implode(',', $ids)]);

                        // Membuka URL di tab baru menggunakan JavaScript redirect
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with([
            'createdBy',
            'period',
            'purchaseItems.item.category',
        ]);
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}