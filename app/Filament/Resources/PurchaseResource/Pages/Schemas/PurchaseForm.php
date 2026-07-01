<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\Period;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{

    public static function configure(Schema $schema)
    {
        return $schema
            ->schema([
                Tabs::make('PurchaseTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Pengadaan Barang')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Form Pengadaan Barang')
                                    ->description('Pengadaan barang dengan harga yang sama akan menambahkan stok barang, jika pengadaan barang memiliki harga yang berbeda dengan harga sebelumnya, silahkan menuju Pengadaan Barang Baru untuk menambahkan pengadaan barang baru')
                                    ->schema([

                                        Grid::make(2)
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
                                                    ->live(debounce: 1000)
                                                    ->afterStateUpdated(
                                                        fn(Get $get, Set $set)
                                                        => self::updateSubtotalAndTotal($get, $set)
                                                    ),

                                                TextInput::make('unit_price')
                                                    ->label('Harga Satuan')
                                                    ->numeric()
                                                    ->required()
                                                    ->readOnly()
                                                    ->rule('decimal:0,2')
                                                    ->prefix('Rp')
                                                    ->live(debounce: 1000)
                                                    ->afterStateUpdated(
                                                        fn(Get $get, Set $set)
                                                        => self::updateSubtotalAndTotal($get, $set)
                                                    ),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->readonly()
                                                    ->dehydrated()
                                                    ->rule('decimal:0,2')
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
                                                ->action(fn($livewire) => $livewire->savePurchaseItems()),
                                        ])->visible(fn($record) => $record=== null),
                                    ]),
                            ]),

                        Tab::make('Pengadaan Barang Baru')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                // SECTION 1: Registrasi Barang Baru
                                Section::make('Daftarkan Barang Baru')
                                    ->description('Daftarkan barang baru sebelum melakukan pengadaan barang')
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('new_item_name')->label('Nama Barang Baru'),
                                                Select::make('new_item_category')->label('Kategori')
                                                    ->searchable()->options(Category::pluck('name', 'id')),
                                                Select::make('new_item_type')->label('Satuan')
                                                    ->searchable()->options(ItemType::pluck('name', 'id')),
                                            ]),
                                    ])
                                    ->headerActions([
                                        FormAction::make('saveNewItem')
                                            ->label('Daftarkan Barang Baru')
                                            ->color('success')
                                            ->icon('heroicon-m-check-circle')
                                            ->action(function (Get $get, Set $set) {
                                                if (!$get('new_item_name')) {
                                                    Notification::make()
                                                        ->title('Gagal!')
                                                        ->body('Nama barang wajib diisi.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                // PERBAIKAN: Dapatkan periode aktif
                                                $activePeriod = Period::query()
                                                    ->where('is_closed', false)
                                                    ->orderByDesc('id')
                                                    ->first();

                                                if (!$activePeriod) {
                                                    Notification::make()
                                                        ->title('Gagal!')
                                                        ->body('Tidak ada periode aktif. Silakan buat periode baru terlebih dahulu.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                // Buat item baru dengan initial_period_id dari periode aktif
                                                Item::create([
                                                    'name' => $get('new_item_name'),
                                                    'category_id' => $get('new_item_category'),
                                                    'item_type_id' => $get('new_item_type'),
                                                    'initial_period_id' => $activePeriod->id, // PERBAIKAN: Set periode aktif
                                                    'price' => 0,
                                                    'stock' => 0,
                                                    'initial_stock' => 0,
                                                ]);

                                                Notification::make()
                                                    ->title('Berhasil!')
                                                    ->body('Barang baru berhasil didaftarkan pada periode ' . $activePeriod->year)
                                                    ->success()
                                                    ->send();

                                                // Reset form input barang baru
                                                $set('new_item_name', null);
                                                $set('new_item_category', null);
                                                $set('new_item_type', null);
                                            }),
                                    ]),

                                // SECTION 2: Form Pengadaan (Sama dengan Tab 1)
                                Section::make('Pengadaan Barang baru')
                                    ->schema([
                                        TextInput::make('total_amount_tab2')
                                            ->readonly()
                                            ->label('Total Pengadaan')
                                            ->numeric()
                                            ->prefix('Rp')
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
                                            ->live()
                                            ->schema([
                                                Select::make('item_id')
                                                    ->label('Pilih Barang yang Baru Dibuat')
                                                    ->reactive()
                                                    ->relationship('item', 'name')
                                                    ->options(
                                                        fn() =>
                                                        Item::where('price', 0)->pluck('name', 'id')
                                                    )
                                                    ->searchable()
                                                    ->getSearchResultsUsing(function (string $search) {
                                                        return Item::query()
                                                            ->where('name', 'like', '%' . $search . '%')
                                                            ->where('price', 0)
                                                            ->limit(5)
                                                            ->pluck('name', 'id');
                                                    })
                                                ,
                                                TextInput::make('qty')
                                                    ->label('Jumlah')->numeric()->default(0)
                                                    ->live(debounce: 1000)
                                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateSubtotalAndTotalTab2($get, $set)),
                                                TextInput::make('unit_price')
                                                    ->label('Harga Satuan')->numeric()->prefix('Rp')
                                                    ->live(debounce: 1000)
                                                    ->rule('decimal:0,2')
                                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateSubtotalAndTotalTab2($get, $set)),
                                                TextInput::make('subtotal')->label('Subtotal')->readonly()->dehydrated()->prefix('Rp')
                                                    ->rule('decimal:0,2'),
                                                TextInput::make('supplier')->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalAmountTab2($get, $set)),
                                        Actions::make([
                                            FormAction::make('saveNewPurchaseItems')
                                                ->label('Simpan Pengadaan')
                                                ->color('primary')
                                                ->action(fn($livewire) => $livewire->saveNewPurchaseItems())
                                        ])->visible(fn($record) => $record=== null),
                                    ])
                            ]),
                    ]),
            ]);
    }

    // Fungsi Hitung Otomatis Subtotal + Update Total
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
}