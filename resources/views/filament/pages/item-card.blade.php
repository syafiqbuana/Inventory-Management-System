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

            <form wire:submit.prevent="generatePDF" class="grid gap-y-6">
                {{ $this->form }}
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>