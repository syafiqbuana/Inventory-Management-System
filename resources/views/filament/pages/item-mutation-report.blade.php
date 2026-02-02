<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Generate Laporan Mutasi Barang
            </x-slot>
            
            <x-slot name="description">
                Laporan ini menampilkan mutasi barang persediaan dari saldo akhir tahun sebelumnya sampai dengan tanggal yang Anda pilih.
            </x-slot>
            
            <form>
                {{ $this->form }}
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button
                        type="button"
                        wire:click="generatePDF"
                        icon="heroicon-o-document-arrow-down"
                        color="info"
                    >
                        Cetak Mutasi PDF
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">
                Informasi Laporan
            </x-slot>
            
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Laporan Mutasi Barang Persediaan berisi informasi berikut:
                </p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1 mt-2">
                    <li><strong>Saldo Akhir 31-12-2024:</strong> Stok awal dari tahun sebelumnya (initial stock)</li>
                    <li><strong>Pengadaan s/d tanggal:</strong> Total pembelian/purchase sampai tanggal yang dipilih</li>
                    <li><strong>Jumlah Sampai s/d tanggal:</strong> Total stok = Saldo Akhir + Pengadaan</li>
                    <li><strong>Jumlah Penggunaan s/d tanggal:</strong> Total pemakaian/usage sampai tanggal yang dipilih</li>
                    <li><strong>Sisa Per tanggal:</strong> Stok akhir = Jumlah Sampai - Jumlah Penggunaan</li>
                </ul>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    Data dikelompokkan berdasarkan kategori barang dan ditampilkan dalam format landscape.
                </p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>