<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\PurchaseItem;
use App\Models\UsageItem;
use App\Models\PeriodStock;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ItemHistoryPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ItemResource::class;
    protected static string $view = 'filament.resources.item-resource.pages.item-history-page';

    public Item $record;

    #[Url]
    public string $activeTab = 'purchase';

    public function getTitle(): string
    {
        return "Riwayat: {$this->record->name}";
    }

    public function resolveRecord(int|string $key): Item
    {
        return Item::with('itemType', 'category')->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ItemResource::getUrl('index')),
        ];
    }

    public function getSubheading(): string
    {
        return "Kategori: {$this->record->category?->name} | Stok Awal: {$this->record->initial_stock}";
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'purchase' => $this->purchaseTable($table),
            'usage' => $this->usageTable($table),
            'stock' => $this->stockTable($table),
            default => $this->purchaseTable($table),
        };
    }
    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    protected function purchaseTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseItem::query()
                    ->with('purchase.createdBy')
                    ->where('item_id', $this->record->id)
            )
            ->columns([
                TextColumn::make('purchase.purchase_date')
                    ->label('Tanggal Pembelian')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Jumlah')
                    ->suffix(' unit'),
                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR'),
                TextColumn::make('supplier')
                    ->label('Supplier'),
                TextColumn::make('purchase.note')
                    ->label('Keterangan')
                    ->limit(40),
                TextColumn::make('purchase.period.year')
                    ->label('Periode'),
            ])
            ->defaultSort('purchase.purchase_date', 'desc')
            ->emptyStateHeading('Belum ada riwayat pembelian')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    protected function usageTable(Table $table): Table
    {
        return $table
            ->query(
                UsageItem::query()
                    ->with('usage.createdBy')
                    ->where('item_id', $this->record->id)
            )
            ->columns([
                TextColumn::make('usage.usage_date')
                    ->label('Tanggal Penggunaan')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Jumlah')
                    ->suffix(' unit'),
                TextColumn::make('usage.used_for')
                    ->label('Digunakan Untuk'),
                TextColumn::make('usage.used_by')
                    ->label('Digunakan Oleh'),
                TextColumn::make('usage.period.year')
                    ->label('Periode'),
                TextColumn::make('usage.createdBy.name')
                    ->label('Diinput Oleh'),
            ])
            ->defaultSort('usage.usage_date', 'desc')
            ->emptyStateHeading('Belum ada riwayat penggunaan')
            ->emptyStateIcon('heroicon-o-archive-box-arrow-down');
    }

    protected function stockTable(Table $table): Table
    {
        return $table
            ->query(
                PeriodStock::query()
                    ->with('period')
                    ->where('item_id', $this->record->id)
            )
            ->columns([
                TextColumn::make('period.year')
                    ->label('Periode/Tahun')
                    ->sortable(),
                TextColumn::make('initial_stock')
                    ->label('Stok Awal'),
                TextColumn::make('final_stock')
                    ->label('Stok Akhir'),
                TextColumn::make('price')
                    ->label('Harga Saat Itu')
                    ->money('IDR'),
            ])
            ->defaultSort('period.year', 'desc')
            ->emptyStateHeading('Belum ada data stock periode')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}