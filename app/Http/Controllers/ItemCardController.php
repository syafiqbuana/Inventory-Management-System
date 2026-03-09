<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Period;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemCardController extends Controller
{
    public function stream(Request $request)
    {
        DB::enableQueryLog();
        
        $periodId = $request->query('period_id');
        $categoryId = $request->query('category_id');

        // Validasi periode
        if (!$periodId) {
            return response()->json([
                'message' => 'Periode harus dipilih'
            ], 400);
        }

        $period = Period::find($periodId);
        
        if (!$period) {
            return response()->json([
                'message' => 'Periode tidak ditemukan'
            ], 404);
        }

        $items = $this->getItems($categoryId, $periodId);

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang untuk dicetak'
            ], 404);
        }

        $itemsData = $this->buildItemCards($items, $period);

        Log::info('Query Log:', DB::getQueryLog());

        $pdf = Pdf::loadView('filament.pages.print-all-items-pdf', [
            'itemsData' => $itemsData,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'period' => $period,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'dpi' => 96,
                'defaultFont' => 'sans-serif'
            ]);

        return $pdf->stream('kartu-barang-periode-' . $period->year . '.pdf');
    }

    public function download(Request $request)
    {
        $periodId = $request->query('period_id');
        $categoryId = $request->query('category_id');

        // Validasi periode
        if (!$periodId) {
            return response()->json([
                'message' => 'Periode harus dipilih'
            ], 400);
        }

        $period = Period::find($periodId);
        
        if (!$period) {
            return response()->json([
                'message' => 'Periode tidak ditemukan'
            ], 404);
        }

        $items = $this->getItems($categoryId, $periodId);

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang untuk dicetak'
            ], 404);
        }

        $itemsData = $this->buildItemCards($items, $period);

        $pdf = Pdf::loadView('filament.pages.print-all-items-pdf', [
            'itemsData' => $itemsData,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'period' => $period,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->download('kartu-barang-periode-' . $period->year . '.pdf');
    }

    /**
     * Query items dengan eager loading minimal
     * Filter berdasarkan periode
     */
    private function getItems($categoryId, $periodId)
    {
        $query = Item::query()
            ->select([
                'id',
                'name',
                'category_id',
                'item_type_id',
                'initial_stock',
                'price',
                'initial_period_id',
                'created_at'
            ])
            ->with([
                'category:id,name',
                'itemType:id,name',

                // Filter purchase items berdasarkan periode
                'purchaseItems' => function ($query) use ($periodId) {
                    $query->select('id', 'item_id', 'purchase_id', 'qty', 'unit_price', 'supplier')
                        ->whereHas('purchase', function ($q) use ($periodId) {
                            $q->where('period_id', $periodId);
                        });
                },
                'purchaseItems.purchase:id,purchase_date,note,period_id',

                // Filter usage items berdasarkan periode
                'usageItems' => function ($query) use ($periodId) {
                    $query->select('id', 'item_id', 'usage_id', 'qty', 'sbbk_number')
                        ->whereHas('usage', function ($q) use ($periodId) {
                            $q->where('period_id', $periodId);
                        });
                },
                'usageItems.usage:id,usage_date,used_for,period_id'
            ])
            ->orderBy('name');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }

    /**
     * Build data kartu barang per periode
     */
    private function buildItemCards($items, $period)
    {
        $result = [];

        foreach ($items as $item) {

            $transactions = [];

            $satuan = $item->itemType?->name ?? '-';

            /* ================= SALDO AWAL ================= */
            // Hanya tampilkan saldo awal jika item ini memiliki initial_period_id yang sama dengan periode yang dipilih
            if ($item->initial_period_id == $period->id && $item->initial_stock > 0) {
                $transactions[] = [
                    'date' => $item->created_at,
                    'reference' => 'SALDO AWAL',
                    'qty_in' => $item->initial_stock,
                    'qty_out' => 0,
                    'unit_price' => $item->price,
                    'notes' => 'Periode ' . $period->year
                ];
            }

            /* ================= PEMBELIAN ================= */
            foreach ($item->purchaseItems as $pi) {
                // purchaseItems sudah difilter di eager loading
                $transactions[] = [
                    'date' => $pi->purchase?->purchase_date,
                    'reference' => $pi->purchase?->note ?? '-',
                    'qty_in' => $pi->qty,
                    'qty_out' => 0,
                    'unit_price' => $pi->unit_price,
                    'notes' => $pi->supplier
                ];
            }

            /* ================= PENGGUNAAN ================= */
            foreach ($item->usageItems as $ui) {
                // usageItems sudah difilter di eager loading
                $transactions[] = [
                    'date' => $ui->usage?->usage_date,
                    'reference' => $ui->sbbk_number ?? '-',
                    'qty_in' => 0,
                    'qty_out' => $ui->qty,
                    'unit_price' => 0,
                    'notes' => $ui->usage?->used_for
                ];
            }

            /* ================= SORT BY DATE ================= */
            usort($transactions, function ($a, $b) {
                return Carbon::parse($a['date'])->timestamp <=> Carbon::parse($b['date'])->timestamp;
            });

            /* ================= HITUNG SALDO ================= */
            $saldo = 0;

            foreach ($transactions as $i => $trx) {
                $saldo += $trx['qty_in'] - $trx['qty_out'];

                $transactions[$i]['saldo'] = $saldo;
                $transactions[$i]['date'] = optional($trx['date'])->format('d/m/Y');
            }

            // Skip item yang tidak memiliki transaksi di periode ini
            if (empty($transactions)) {
                continue;
            }

            $result[] = [
                'item' => $item,
                'satuan' => $satuan,
                'transactions' => $transactions,
                'finalStock' => $saldo
            ];
        }

        return $result;
    }
}