<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use App\Models\Period;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class ItemMutationReport extends Page implements HasForms
{
    protected static string $view = 'filament.pages.item-mutation-report';
    
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $navigationLabel = 'Laporan Mutasi';
    
    protected static ?string $navigationGroup = 'Laporan';
    
    protected static ?string $pluralModelLabel = 'Laporan Mutasi Barang';
    
    protected static ?int $navigationSort = 10;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        // Set default values
        $activePeriod = Period::where('is_closed', false)->first();
        
        $this->form->fill([
            'periode' => $activePeriod?->id,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->description('Pilih periode dan tanggal akhir untuk laporan mutasi barang persediaan')
                    ->schema([
                        Select::make('periode')
                            ->label('Periode')
                            ->options(Period::orderByDesc('year')->pluck('year', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih Periode')
                            ->helperText('Pilih periode untuk melihat mutasi barang pada periode tersebut')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $period = Period::find($state);
                                if ($period) {
                                    $set('tanggal_akhir', $period->year . '-12-31');
                                }
                            }),
                        
                        DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir Laporan')
                            ->required()
                            ->placeholder('Pilih Tanggal')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->helperText('Laporan akan menampilkan mutasi barang dari saldo awal sampai tanggal yang dipilih'),
                    ])
                    ->columns(2)
            ])
            ->statePath('data');
    }
    
    public function generatePDF(): void
    {
        $data = $this->form->getState();
        
        // Validasi apakah periode sudah dipilih
        if (empty($data['periode'])) {
            Notification::make()
                ->title('Periode harus dipilih')
                ->danger()
                ->send();
            return;
        }
        
        // Validasi apakah tanggal akhir sudah dipilih
        if (empty($data['tanggal_akhir'])) {
            Notification::make()
                ->title('Tanggal akhir harus dipilih')
                ->danger()
                ->send();
            return;
        }
        
        // Generate URL untuk laporan dengan parameter periode
        $url = route('reports.mutasi-barang', [
            'periode' => $data['periode'],
            'tanggal_akhir' => $data['tanggal_akhir']
        ]);
        
        // Buka di tab baru menggunakan JavaScript
        $this->js("window.open('$url', '_blank')");
    
    }
}