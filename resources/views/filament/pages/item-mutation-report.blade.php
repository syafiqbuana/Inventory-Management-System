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
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>