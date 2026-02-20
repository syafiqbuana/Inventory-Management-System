<x-filament-panels::page>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-filament::card>
        <div class="text-sm text-gray-500">Total Pembelian</div>
        <div class="text-2xl font-bold text-primary-600">
            {{ $this->record->purchaseItems()->sum('qty') }} {{ $this->record->itemType?->name ?? 'unit' }}
        </div>
    </x-filament::card>
    <x-filament::card>
        <div class="text-sm text-gray-500">Total Penggunaan</div>
        <div class="text-2xl font-bold text-warning-600">
            {{ $this->record->usageItems()->sum('qty') }} {{ $this->record->itemType?->name ?? 'unit' }}
        </div>
    </x-filament::card>
    <x-filament::card>
        <div class="text-sm text-gray-500">Stok Awal (Input Pertama)</div>
        <div class="text-2xl font-bold text-success-600">
            {{ $this->record->initial_stock }} {{ $this->record->itemType?->name ?? 'unit' }}
        </div>
        <div class="text-xs text-gray-400 mt-1">
            {{ $this->record->created_at?->translatedFormat('d F Y') }}
        </div>
    </x-filament::card>
</div>
    {{-- Tabs --}}
    <div class="flex gap-2 mb-4">
        @foreach([
            'purchase' => ['label' => 'Riwayat Pembelian', 'icon' => 'heroicon-o-shopping-cart'],
            'usage'    => ['label' => 'Riwayat Penggunaan', 'icon' => 'heroicon-o-archive-box-arrow-down'],
            'stock'    => ['label' => 'Stock Per Periode', 'icon' => 'heroicon-o-archive-box'],
        ] as $tab => $config)
            <button
                wire:click="setTab('{{ $tab }}')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-primary-600 text-white shadow' => $activeTab === $tab,
                    'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600' => $activeTab !== $tab,
                ])
            >
                {{ $config['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    {{ $this->table }}

</x-filament-panels::page>