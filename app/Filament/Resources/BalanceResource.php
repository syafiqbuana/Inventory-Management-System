<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Models\Balance;
use App\Models\Income;
use App\Models\Purchase;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'Saldo';
    


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label('Jumlah Saldo')
                    ->required()
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Saldo Saat Ini')
                    ->money('idr', true)
                    ->color('success')
                    ->weight('bold')
                    ->size('lg')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d F Y H:i')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d F Y H:i')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Filter Rentang Tanggal')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Ekspor ke PDF')
                    ->color('danger')
                    ->icon('heroicon-o-printer')
                    ->action(function () use ($table) {
                        $livewire = $table->getLivewire();
                        $appliedFilters = $livewire->tableFilters ?? [];
                        $keteranganFilter = [];
                        
                        // Default periode (bulan ini jika tidak ada filter)
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        
                        // Filter Tanggal
                        if (isset($appliedFilters['created_at']['created_at'])) {
                            $dateRangeString = $appliedFilters['created_at']['created_at'];

                            if ($dateRangeString) {
                                $dates = explode(' - ', $dateRangeString);
                                if (count($dates) === 2) {
                                    $start = trim($dates[0]);
                                    $end = trim($dates[1]);

                                    $startDate = Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
                                    $endDate = Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

                                    $formattedStart = $startDate->translatedFormat('d F Y');
                                    $formattedEnd = $endDate->translatedFormat('d F Y');

                                    $keteranganFilter[] = 'Filter Tanggal: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
                                }
                            }
                        }
                        
                        // Ambil balance (hanya 1 row)
                        $balance = Balance::first();
                        
                        if (!$balance) {
                            // Jika tidak ada balance, buat default
                            $balance = new Balance();
                            $balance->amount = 0;
                            $balance->updated_at = now();
                        }
                        
                        // Ambil semua income dan purchase dalam periode
                        $incomes = Income::whereBetween('created_at', [$startDate, $endDate])->get();
                        $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])->get();
                        
                        // Hitung total
                        $totalIncome = $incomes->sum('amount');
                        $totalPurchase = $purchases->sum('total_amount');
                        $incomeCount = $incomes->count();
                        $purchaseCount = $purchases->count();
                        
                        // Hitung saldo awal periode (saldo saat ini - perubahan dalam periode)
                        $initialBalance = $balance->amount - ($totalIncome - $totalPurchase);
                        
                        // Gabungkan transaksi dan urutkan berdasarkan tanggal
                        $transactions = collect();
                        
                        foreach ($incomes as $income) {
                            $transactions->push((object)[
                                'created_at' => $income->created_at,
                                'type' => 'income',
                                'amount' => $income->amount,
                                'source' => $income->source,
                                'note' => null,
                            ]);
                        }
                        
                        foreach ($purchases as $purchase) {
                            $transactions->push((object)[
                                'created_at' => $purchase->created_at,
                                'type' => 'purchase',
                                'amount' => $purchase->total_amount,
                                'source' => null,
                                'note' => $purchase->note,
                            ]);
                        }
                        
                        // Urutkan berdasarkan tanggal
                        $transactions = $transactions->sortBy('created_at')->values();
                        
                        // Generate PDF
                        $pdf = App::make('dompdf.wrapper');
                        $pdf->loadView('pdf.balance_report', compact(
                            'balance',
                            'transactions',
                            'totalIncome',
                            'totalPurchase',
                            'incomeCount',
                            'purchaseCount',
                            'initialBalance',
                            'keteranganFilter'
                        ))->setPaper('a4', 'portrait');
                        
                        $fileName = 'Laporan_Saldo_' . now()->format('Ymd_His') . '.pdf';
                        
                        return response()->streamDownload(function () use ($pdf, $fileName) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),
            ])
            ->emptyStateHeading('Belum ada data saldo')
            ->emptyStateDescription('Silakan tambahkan data saldo terlebih dahulu')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
    
    public static function canCreate(): bool
    {
        // Cegah pembuatan balance baru jika sudah ada
        return Balance::count() === 0;
    }
}