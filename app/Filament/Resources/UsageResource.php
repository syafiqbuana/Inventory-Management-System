<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageResource\Pages;
use App\Filament\Resources\UsageResource\RelationManagers;
use App\Models\Usage;
use App\Models\Category;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class UsageResource extends Resource
{
    protected static ?string $model = Usage::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Penggunaan Barang';
    protected static ?string $modelLabel = 'Penggunaan Barang';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $pluralModelLabel = 'Penggunaan Barang';
    public static function form(Form $form): Form
    {
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('used_by')
                    ->label('Diambil Oleh')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('used_for')
                    ->label('Digunakan Untuk')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 40) {
                            return $state;
                        }
                        return null;
                    }),

                //menampilkan list item yang digunakan menggunakan list
                Tables\Columns\TextColumn::make('usageItems.item.name')
                    ->label('Item')
                    ->getStateUsing(function (Usage $record): array {
                        return $record->usageItems->map(fn($item) => $item->item->name ?? 'Item Tidak Dikenal')->toArray()
                        ;
                    })
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->listWithLineBreaks()
                    ->bulleted(),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Barang')
                    ->getStateUsing(function (Usage $record): int {
                        return $record->usageItems->sum('qty');
                    })
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('createdBy')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('usage_date', 'desc')
            ->filters([
                // Filter Kategori
                SelectFilter::make('category')
                    ->label('Kategori Item')
                    ->relationship('usageItems.item.category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Filter Pengguna
                SelectFilter::make('used_by')
                    ->label('Pengguna')
                    ->options(function () {
                        return Usage::query()
                            ->distinct()
                            ->pluck('used_by', 'used_by')
                            ->toArray();
                    })
                    ->searchable(),

                // Filter Rentang Tanggal
                DateRangeFilter::make('usage_date')
                    ->label('Rentang Tanggal Penggunaan'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('warning')
                    ->disabled(fn(Usage $record) => $record->period->is_closed),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->disabled(fn(Usage $record) => $record->period->is_closed)
                    ->tooltip('Period is closed')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Penggunaan Baru')
                    ->icon('heroicon-m-plus'),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Ekspor ke PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->url(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $appliedFilters = $livewire->tableFilters ?? [];

                        $params = [];
                        if (isset($appliedFilters['category']['values']) && !empty($appliedFilters['category']['values'])) {
                            $params['categories'] = $appliedFilters['category']['values'];
                        }

                        
                        if (isset($appliedFilters['used_by']['value']) && $appliedFilters['used_by']['value'] !== null) {
                            $params['used_by'] = $appliedFilters['used_by']['value'];
                        }

                    
                        if (isset($appliedFilters['usage_date']['usage_date'])) {
                            $dateRangeString = $appliedFilters['usage_date']['usage_date'];
                            if ($dateRangeString) {
                                $params['date_range'] = $dateRangeString;
                            }
                        }

                        return route('usage.report.export', $params);
                    })
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Bisa tambahkan RelationManager jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsages::route('/'),
            'create' => Pages\CreateUsage::route('/create'),
            'view' => Pages\ViewUsage::route('/{record}'),
            'edit' => Pages\EditUsage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['usageItems.item.category', 'createdBy']);
    }
}