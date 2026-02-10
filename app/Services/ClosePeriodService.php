<?php

namespace App\Services;

use App\Models\Period;
use App\Models\Item;
use App\Models\PeriodStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ClosePeriodService
{
    public function close(Period $period): Period
    {
        if ($period->is_closed) {
            throw ValidationException::withMessages([
                'period' => 'This period is already closed.',
            ]);
        }

        return DB::transaction(function () use ($period) {

            $items = Item::query()
                ->withSum([
                    'purchaseItems as purchased_qty' => function ($q) use ($period) {
                        $q->whereHas(
                            'purchase',
                            fn($p) =>
                            $p->where('period_id', $period->id)
                        );
                    }
                ], 'qty')
                ->withSum([
                    'usageItems as used_qty' => function ($q) use ($period) {
                        $q->whereHas(
                            'usage',
                            fn($u) =>
                            $u->where('period_id', $period->id)
                        );
                    }
                ], 'qty')
                ->get();

            /**
             * 2️⃣ Create snapshot for current period (SEBELUM close)
             * Ini akan menyimpan data historis periode yang akan ditutup
             */
            foreach ($items as $item) {
                $initialStock = ($item->initial_period_id == $period->id)
                    ? $item->initial_stock
                    : 0;

                $finalStock = $initialStock
                    + ($item->purchased_qty ?? 0)
                    - ($item->used_qty ?? 0);

                // Simpan snapshot untuk periode yang akan ditutup
                PeriodStock::updateOrCreate(
                    [
                        'period_id' => $period->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'initial_stock' => $initialStock,
                        'final_stock' => max(0, $finalStock), // Pastikan tidak negatif
                        'price' => $item->price,
                    ]
                );
            }

            /**
             * 3️⃣ Close current period
             */
            $period->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

            /**
             * 4️⃣ Create next period
             */
            $nextPeriod = Period::create([
                'year' => $period->year + 1,
                'is_closed' => false,
            ]);

            foreach ($items as $item) {
                // Ambil final_stock dari snapshot yang baru dibuat
                $periodStock = PeriodStock::where('period_id', $period->id)
                    ->where('item_id', $item->id)
                    ->first();

                $finalStock = $periodStock->final_stock ?? 0;

                // Update item untuk periode baru
                if ($finalStock > 0) {
                    $item->update([
                        'initial_stock' => $finalStock,
                        'initial_period_id' => $nextPeriod->id,
                    ]);
                } else {
                    // Jika stock habis, tetap set initial_period_id tapi stock = 0
                    $item->update([
                        'initial_stock' => 0,
                        'initial_period_id' => $nextPeriod->id,
                    ]);
                }
            }

            foreach ($items as $item) {
                $periodStock = PeriodStock::where('period_id', $period->id)
                    ->where('item_id', $item->id)
                    ->first();

                if ($periodStock && $periodStock->final_stock > 0) {
                    PeriodStock::create([
                        'period_id' => $nextPeriod->id,
                        'item_id' => $item->id,
                        'initial_stock' => $periodStock->final_stock,
                        'final_stock' => $periodStock->final_stock, // Akan diupdate saat close
                        'price' => $item->price,
                    ]);
                }
            }

            cache()->forget('active_period');
            Period::forgetActive();

            return $nextPeriod;
        });
    }
}