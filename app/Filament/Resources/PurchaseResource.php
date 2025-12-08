<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Models\PurchaseItem;
use Filament\Tables;

use App\Filament\Exports\PurchaseExporter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PurchaseResource\Pages\CreatePurchase;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Get;
use Illuminate\Support\Facades\App;
use Filament\Tables\Actions\Action;
use Filament\Forms\Set;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('note')->required(),
                        TextInput::make('total_amount')
                            ->label('Total')
                            ->prefix('Rp')
                            ->readonly()
                            ->dehydrated(),
                    ]),
                Forms\Components\Grid::make()
                    ->schema([
                        Repeater::make('purchaseItems')
                            ->relationship()
                            ->reactive()
                            ->default([])
                            ->schema([
                                Select::make('item_id')
                                    ->relationship('item', 'name')
                                    ->required(),

                                TextInput::make('qty')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = floatval($get('unit_price') ?? 0);
                                        $qty = floatval($state ?? 0);
                                        $set('subtotal', $qty * $price);
                                    }),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = floatval($state ?? 0);
                                        $qty = floatval($get('qty') ?? 0);
                                        $set('subtotal', $qty * $price);
                                    }),

                                TextInput::make('subtotal')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->numeric()
                                    ->reactive()
                                    ->prefix('Rp')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        // SOLUSI: Hitung total setiap kali subtotal berubah
                                        self::updateTotalAmount($get, $set);
                                    })
                                ,
                            ])
                            ->columnSpanFull()
                            ->required()
                            ->live(debounce: 100)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Backup: Hitung juga saat repeater berubah (add/delete)
                                self::updateTotalAmount($get, $set);
                            })
                            ->deleteAction(
                                fn($action) => $action->after(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set))
                            )
                            ->addAction(
                                fn($action) => $action->after(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set))
                            ),
                    ])
            ]);
    }

    // Helper function untuk menghitung total
    protected static function updateTotalAmount(Get $get, Set $set): void
    {
        $items = $get('purchaseItems') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('total_amount', round($total, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('item_names')
                    ->label('Items Dibeli')
                    ->getStateUsing(function (Purchase $record): array {
                        return $record->purchaseItems->map(function ($item) {
                            return $item->item->name ?? 'Item Tidak Dikenal';
                        })->toArray();
                    })
                    ->listWithLineBreaks()
                    ->bulleted(),

                Tables\Columns\TextColumn::make('item_quantities')
                    ->label('Jumlah Dibeli')
                    ->getStateUsing(function (Purchase $record): array {
                        return $record->purchaseItems->map(function ($item) {
                            return $item->qty ?? 0;
                        })->toArray();
                    })
                    ->listWithLineBreaks()
                    ->badge()->color('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('unit_prices')
                    ->label('Harga Unit')
                    ->getStateUsing(function (Purchase $record): array {
                        return $record->purchaseItems->map(function ($item) {
                            $unitPrice = $item->unit_price ?? 0;
                            $formattedUnitPrice = 'IDR ' . number_format($unitPrice, 0, ',', '.');
                            return $formattedUnitPrice;
                        })->toArray();
                    })
                    ->listWithLineBreaks()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('item_subtotals')
                    ->label('Subtotal Item')
                    ->getStateUsing(function (Purchase $record): array {
                        return $record->purchaseItems->map(function ($item) {
                            $subtotal = $item->subtotal ?? 0;
                            $formattedSubtotal = 'IDR ' . number_format($subtotal, 0, ',', '.');
                            return "{$formattedSubtotal}";
                        })->toArray();
                    })
                    ->listWithLineBreaks()
                    ->alignCenter()
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('total_amount')->label('Total Amount')->money('idr', true)->alignCenter(),
                Tables\Columns\TextColumn::make('note')->label('Note')->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->date()->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->date()->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([

            ])->headerActions([
Action::make('export_pdf') // <-- Tambahkan Action Kustom ini
                ->label('Ekspor ke PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () use ($table) {
                    // 1. Ambil data dari query tabel saat ini (termasuk filter yang aktif)
                    $query = $table->getLivewire()->getFilteredTableQuery();
                    
                    // Gunakan with() untuk memuat relasi agar tidak ada N+1 query
                    $records = $query->with('purchaseItems.item')->get(); 

                    // 2. Render view HTML menggunakan DomPDF
                    $pdf = App::make('dompdf.wrapper');
                    $pdf->loadView('pdf.purchase_report', compact('records'));

                    // 3. Download file
                    $fileName = 'Laporan_Pembelian_' . now()->format('Ymd_His') . '.pdf';
                    return response()->streamDownload(function () use ($pdf) {
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            // 'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}