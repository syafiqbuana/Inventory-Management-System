<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Balance;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    // Property untuk menyimpan total lama
    public $oldTotalAmount = 0;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Kembalikan saldo saat hapus purchase
                    DB::transaction(function () {
                        $totalAmount = $this->record->total_amount;
                        
                        if ($totalAmount > 0) {
                            $balance = Balance::first();
                            $balance->increment('amount', $totalAmount);
                            
                            Notification::make()->success()
                                ->title('Saldo Dikembalikan')
                                ->body("Saldo Rp" . number_format($totalAmount, 0, ',', '.') . " telah dikembalikan.")
                                ->send();
                        }
                    });
                }),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        $this->oldTotalAmount = floatval($this->record->total_amount ?? 0);
        
        Log::info('Mount - Old Total: ' . $this->oldTotalAmount);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hitung selisih antara total baru dan total lama
        $oldTotal = $this->oldTotalAmount;
        $newTotal = floatval($data['total_amount'] ?? 0);
        $difference = $newTotal - $oldTotal;

        //debug log
        Log::info("Before Save - Old: {$oldTotal}, New: {$newTotal}, Diff: {$difference}");

        // Jika total baru lebih besar, pastikan saldo mencukupi
        if ($difference > 0) {
            $balance = Balance::first();

            if (!$balance) {
                Notification::make()->danger()
                    ->title('Saldo belum dibuat!')
                    ->send();
                $this->halt();
            }

            if ($balance->amount < $difference) {
                Notification::make()->danger()
                    ->title('Saldo tidak mencukupi!')
                    ->body("Perlu tambahan Rp" . number_format($difference, 0, ',', '.') . ", tetapi saldo hanya Rp" . number_format($balance->amount, 0, ',', '.'))
                    ->send();
                $this->halt();
            }
        }

        if ($newTotal <= 0) {
            Notification::make()->warning()
                ->title('Total pembelian harus lebih dari 0!')
                ->send();
            $this->halt();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            $oldTotal = $this->oldTotalAmount;
            $newTotal = floatval($this->record->total_amount ?? 0);
            $difference = $newTotal - $oldTotal;

            // Debug log 
            Log::info("After Save - Old: {$oldTotal}, New: {$newTotal}, Diff: {$difference}");

            if ($difference != 0) {
                $balance = Balance::first();

                if ($difference > 0) {
                    // Total bertambah (misal 1000 -> 1500), kurangi saldo tambahan 500
                    $balance->decrement('amount', $difference);
                    
                    Notification::make()->success()
                        ->title('Pembelian Diperbarui')
                        ->body("Total bertambah Rp" . number_format($difference, 0, ',', '.') . ". Saldo berkurang.")
                        ->send();
                } else {
                    // Total berkurang (misal 1000 -> 500), kembalikan saldo 500
                    $absAmount = abs($difference);
                    $balance->increment('amount', $absAmount);
                    
                    Notification::make()->success()
                        ->title('Pembelian Diperbarui')
                        ->body("Total berkurang Rp" . number_format($absAmount, 0, ',', '.') . ". Saldo bertambah kembali.")
                        ->send();
                }

                // Update oldTotalAmount untuk edit berikutnya tanpa reload
                $this->oldTotalAmount = $newTotal;
            } else {
                Notification::make()->info()
                    ->title('Pembelian Diperbarui')
                    ->body("Tidak ada perubahan total, saldo tetap.")
                    ->send();
            }
        });
    }
}