<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Category;
use App\Models\Item;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Models\Period;
use Filament\Notifications\Notification;

class ItemCard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Kartu Barang';
    protected static ?string $title = 'Kartu Barang';
    protected static string $view = 'filament.pages.item-card';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->description('Pilih periode dan kategori barang')
                    ->schema([

                        Select::make('period_id')
                            ->label('Periode')
                            ->options(Period::pluck('year', 'id'))
                            ->searchable()
                            ->default(Period::active()?->id)
                            ->required(),

                        Select::make('selected_category_id')
                            ->label('Kategori Barang')
                            ->placeholder('Semua kategori')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),
            ])
            ->statePath('data');
    }

    /**
     * Generate PDF - Redirect ke controller route
     * Mengikuti pola yang sama dengan ItemMutationReport
     */
    public function generatePDF(): void
    {
        $data = $this->form->getState();

        if (empty($data['period_id'])) {
            Notification::make()
                ->title('Periode belum dipilih')
                ->danger()
                ->send();
            return;
        }

        $query = Item::query();

        if (!empty($data['selected_category_id'])) {
            $query->where('category_id', $data['selected_category_id']);
        }

        $itemCount = $query->count();

        if ($itemCount === 0) {
            Notification::make()
                ->title('Tidak ada barang')
                ->danger()
                ->send();
            return;
        }

        $params = [
            'period_id' => $data['period_id']
        ];

        if (!empty($data['selected_category_id'])) {
            $params['category_id'] = $data['selected_category_id'];
        }

        $url = route('print-all-items.stream', $params);

        $this->js("window.open('$url', '_blank')");
    }
}