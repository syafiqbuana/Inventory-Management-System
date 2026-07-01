<?php

use App\Models\Item;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UsageForm{
    public static function configure(Schema $form): Schema{
        return $form
                    ->schema([
                TextInput::make('used_by')
                    ->label('Diambil Oleh')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Masukkan nama Pengambil Barang'),

                DatePicker::make('usage_date')
                    ->label('Tanggal Penggunaan')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                TextInput::make('used_for')
                    ->label('Digunakan Untuk')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Proyek Renovasi Kantor')
                    ->helperText('Tujuan/keperluan penggunaan barang-barang ini')
                    ->columnSpanFull(),

                Repeater::make('usageItems')
                    ->relationship()
                    ->label('Item yang Digunakan')
                    ->schema([
                        Select::make('item_id')
                            ->label('Pilih Item')
                            ->relationship('item', 'name')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return Item::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->withSum('purchaseItems', 'qty')
                                    ->withSum('usageItems', 'qty')
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn($item) => [
                                        $item->id => sprintf(
                                            '%s %s | Rp %s',
                                            $item->name,

                                            $item->type,
                                            number_format($item->price, 0, ',', '.')
                                        ),
                                    ]);
                            })
                            ->placeholder('Ketik nama barang untuk mencari...')

                            ->required()
                            ->reactive()
                            ->columnSpan(2),
                        TextInput::make('sbbk_number')
                            ->label('No. SBBK')
                            ->placeholder('Masukkan nomor SBBK')
                            ->columnSpan(2)
                            ->required(),
                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->addActionLabel('+ Tambah Item')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['item_id']
                        ? Item::find($state['item_id'])?->name
                        : 'Item Baru'
                    ),
            ]);
    }
}