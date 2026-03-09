<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ItemHistoryReportController extends Controller
{
    public function stream(Item $item)
    {
        // Load relasi yang diperlukan
        $item->load([
            'category',
            'itemType',
            'purchaseItems.purchase.period',
            'usageItems.usage',
        ]);

        $satuan = $item->itemType?->name ?? 'unit';

        // DEBUG 1: Log item info
        Log::info('=== START KARTU BARANG DEBUG ===');
        Log::info('Item ID: ' . $item->id);
        Log::info('Initial Stock: ' . $item->initial_stock);
        Log::info('Item Created At: ' . $item->created_at);
        Log::info('Item Price: ' . $item->price);

        // Gabungkan dan urutkan semua transaksi (pembelian + penggunaan)
        $transactions = collect();

        // Tambahkan saldo awal
        if ($item->initial_stock > 0) {
            $transactions->push([
                'type' => 'saldo_awal',
                'date' => $item->created_at,
                'reference' => 'SALDO AWAL',
                'purpose' => null,
                'qty_in' => $item->initial_stock,
                'qty_out' => 0,
                'unit_price' => $item->price,
                'sisa' => 0, // akan dihitung secara progresif
                'notes' => null,
            ]);
        }

        // Tambahkan pembelian
        foreach ($item->purchaseItems as $pi) {
            Log::info('PurchaseItem: qty=' . $pi->qty . ', date=' . $pi->purchase?->purchase_date);
            $transactions->push([
                'type' => 'pembelian',
                'date' => $pi->purchase?->purchase_date,
                'reference' => $pi->purchase?->reference_number ?? '-',
                'purpose' => null,
                'qty_in' => $pi->qty,
                'qty_out' => 0,
                'unit_price' => $pi->unit_price,
                'sisa' => 0,
                'notes' => $pi->supplier ?? null,
            ]);
        }

        // Tambahkan penggunaan
        foreach ($item->usageItems as $ui) {
            Log::info('UsageItem: qty=' . $ui->qty . ', date=' . $ui->usage?->usage_date);
            $transactions->push([
                'type' => 'penggunaan',
                'date' => $ui->usage?->usage_date,
                'reference' => $ui->sbbk_number ?? $ui->usage?->reference_number ?? '-',
                'purpose' => $ui->usage?->used_for,
                'qty_in' => 0,
                'qty_out' => $ui->qty,
                'unit_price' => 0,
                'sisa' => 0,
                'notes' => $ui->usage?->used_for ?? null,
            ]);
        }

        // SORT BERDASARKAN TANGGAL UNTUK URUTAN KRONOLOGIS
        $transactions = $transactions->sortBy(function ($trans) {
            if ($trans['type'] === 'saldo_awal') {
                return Carbon::parse('1900-01-01');
            }
            return Carbon::parse($trans['date']);
        })->values();

        // DEBUG 3: Log SESUDAH sorting
        Log::info('=== AFTER SORTING ===');
        $transactions->each(function ($t, $i) {
            Log::info("[$i] Type: {$t['type']}, Date: {$t['date']}, Qty In: {$t['qty_in']}, Qty Out: {$t['qty_out']}");
        });

        // Hitung sisa stok secara progresif
        // Konversi Collection ke Array DULU
        $transactionsArray = $transactions->toArray();

        $sisa = 0;

        // Loop dengan reference pada ARRAY (bukan Collection)
        foreach ($transactionsArray as &$transaction) {
            $sisa += $transaction['qty_in'] - $transaction['qty_out'];
            $transaction['sisa'] = $sisa;
        }

        // Finalize reference
        unset($transaction);

        // Konversi KEMBALI ke Collection
        $transactions = collect($transactionsArray);



        // Hitung total
        $totalQtyIn = $transactions->sum('qty_in');
        $totalQtyOut = $transactions->sum('qty_out');
        $finalStock = $sisa;


        $pdf = Pdf::loadView(
            'filament.resources.item-resource.report.item-history-report',
            [
                'item' => $item,
                'satuan' => $satuan,
                'transactions' => $transactions,
                'totalQtyIn' => $totalQtyIn,
                'totalQtyOut' => $totalQtyOut,
                'finalStock' => $finalStock,
                'generatedAt' => now()->translatedFormat('d F Y, H:i'),
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->stream("kartu-barang-{$item->id}.pdf");
    }
}