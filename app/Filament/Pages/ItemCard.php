<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Item;
use App\Models\Period;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class ItemCard extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }
    public static function getNavigationLabel(): string
    {
        return 'Kartu Barang';
    }
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Kartu Barang';
    }
    protected string $view = 'filament.pages.item-card';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
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
                    Action::make('generate_pdf')
                        ->label('Generate PDF')
                        ->icon('heroicon-o-printer')
                        ->action(fn () => 
                            $this->generatePDF()
                        )
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