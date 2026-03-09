<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Main Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Cetak Kartu Barang
            </x-slot>
            
            <x-slot name="description">
                Pilih kategori atau biarkan kosong untuk mencetak semua barang dalam format PDF
            </x-slot>
            
            <form>
                {{ $this->form }}
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button
                        type="button"
                        wire:click="generatePDF"
                        icon="heroicon-o-document-arrow-down"
                        color="success"
                    >
                        Cetak PDF
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
        
        {{-- Info Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Informasi Kartu Barang
            </x-slot>
            
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Kartu Barang berisi informasi detail transaksi untuk setiap barang:
                </p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1 mt-2">
                    <li><strong>Saldo Awal:</strong> Stok awal barang (initial stock)</li>
                    <li><strong>Pembelian:</strong> Semua transaksi pembelian dengan nomor referensi dan harga satuan</li>
                    <li><strong>Penggunaan:</strong> Semua transaksi pemakaian dengan nomor SBBK dan keperluan</li>
                    <li><strong>Sisa Stok:</strong> Perhitungan otomatis stok tersisa setelah setiap transaksi</li>
                </ul>
        
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    <strong>Cara Menyimpan:</strong> PDF akan dibuka di tab baru. Gunakan tombol download dari browser untuk menyimpan file.
                </p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>