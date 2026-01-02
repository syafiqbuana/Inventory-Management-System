<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Support\Facades\App;
use Filament\Tables\Actions\Action;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('source')
                    ->label('Source')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('idr', true)
                    ->color('success')
                    ,
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Filter Rentang Tanggal')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Ekspor ke PDF')
                    ->color('danger')
                    ->icon('heroicon-o-printer')
                    ->action(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $query = $livewire->getFilteredTableQuery();
                        $appliedFilters = $livewire->tableFilters ?? [];
                        $keteranganFilter = [];
                        $records = $query->get();

                        // Filter Tanggal
                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];

                            if ($dateRangeString) {
                                $dates = explode(' - ', $dateRangeString);
                                if (count($dates) === 2) {
                                    $start = trim($dates[0]);
                                    $end = trim($dates[1]);

                                    $formattedStart = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->translatedFormat('d F Y');
                                    $formattedEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->translatedFormat('d F Y');

                                    $keteranganFilter[] = 'Filter Tanggal Pemasukan: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
                                }
                            }
                        }

                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadView('pdf.income_report', compact('records', 'keteranganFilter'));

                        $fileName = 'Laporan_Pemasukan_' . now()->format('Ymd_His') . '.pdf';

                        return response()->streamDownload(function () use ($pdf, $fileName) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}