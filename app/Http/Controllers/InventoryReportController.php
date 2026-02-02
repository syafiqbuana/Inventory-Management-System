<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PurchaseItem;
use App\Models\Period;
use App\Models\PeriodStock;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function mutasiBarang(Request $request)
    {
        $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfYear()->format('Y-m-d'));
        $periodeId = $request->input('periode');
        
        $tanggalAkhirCarbon = Carbon::parse($tanggalAkhir);

        // Ambil data periode yang dipilih
        $periode = Period::find($periodeId);
        
        if (!$periode) {
            abort(404, 'Periode tidak ditemukan');
        }

        $categories = Category::with([
            'items' => fn ($q) => $q->orderBy('id')
        ])->orderBy('id')->get();

        $reportData = [];

        foreach ($categories as $category) {

            $categoryData = [
                'name' => $category->name,
                'items' => [],
                'totals' => [
                    'saldo_akhir_vol' => 0,
                    'saldo_akhir_total' => 0,
                    'pengadaan_vol' => 0,
                    'pengadaan_total' => 0,
                    'jumlah_sampai_vol' => 0,
                    'jumlah_sampai_total' => 0,
                    'jumlah_penggunaan_vol' => 0,
                    'jumlah_penggunaan_total' => 0,
                    'sisa_per_vol' => 0,
                    'sisa_per_total' => 0,
                ],
            ];

            foreach ($category->items as $item) {

                /* ================= 1. SALDO AKHIR ================= */
                /**
                 * SOLUSI UNTUK DATA HISTORIS:
                 * 
                 * Jika periode sudah ditutup (is_closed = true):
                 *   → Ambil dari tabel period_stocks (data historis)
                 * 
                 * Jika periode masih aktif (is_closed = false):
                 *   → Hitung dari item.initial_stock (real-time)
                 */
                if ($periode->is_closed) {
                    // ✅ Periode sudah ditutup → ambil dari snapshot
                    $periodStock = PeriodStock::where('period_id', $periode->id)
                        ->where('item_id', $item->id)
                        ->first();
                    
                    $saldoAkhirVol = $periodStock->initial_stock ?? 0;
                    $saldoAkhirHarga = $periodStock->price ?? 0;
                } else {
                    // ✅ Periode masih aktif → hitung real-time
                    $saldoAkhirVol = ($item->initial_period_id == $periode->id) 
                        ? $item->initial_stock 
                        : 0;
                    $saldoAkhirHarga = $item->price;
                }
                
                // Conditional rendering
                if ($saldoAkhirVol > 0) {
                    $saldoAkhirTotal = $saldoAkhirVol * $saldoAkhirHarga;
                } else {
                    $saldoAkhirHarga = 0;
                    $saldoAkhirTotal = 0;
                }

                /* ================= 2. PENGADAAN ================= */
                $pengadaanData = PurchaseItem::where('item_id', $item->id)
                    ->whereHas('purchase', fn ($q) =>
                        $q->where('period_id', $periode->id)
                          ->whereDate('purchase_date', '<=', $tanggalAkhir)
                    )
                    ->selectRaw('
                        SUM(qty) as total_qty,
                        SUM(qty * unit_price) as total_amount
                    ')
                    ->first();

                $pengadaanVol = $pengadaanData->total_qty ?? 0;
                
                if ($pengadaanVol > 0) {
                    $pengadaanTotal = $pengadaanData->total_amount ?? 0;
                    $pengadaanHarga = round($pengadaanTotal / $pengadaanVol);
                } else {
                    $pengadaanHarga = 0;
                    $pengadaanTotal = 0;
                }

                /* ================= 3. JUMLAH SAMPAI ================= */
                $jumlahSampaiVol = $saldoAkhirVol + $pengadaanVol;
                
                if ($jumlahSampaiVol > 0) {
                    $jumlahSampaiTotal = $saldoAkhirTotal + $pengadaanTotal;
                    $jumlahSampaiHarga = round($jumlahSampaiTotal / $jumlahSampaiVol);
                } else {
                    $jumlahSampaiHarga = 0;
                    $jumlahSampaiTotal = 0;
                }

                /* ================= 4. PENGGUNAAN ================= */
                $penggunaanData = DB::table('usage_items as ui')
                    ->join('usages as u', 'u.id', '=', 'ui.usage_id')
                    ->where('ui.item_id', $item->id)
                    ->where('u.period_id', $periode->id)
                    ->whereDate('u.usage_date', '<=', $tanggalAkhir)
                    ->selectRaw('
                        SUM(ui.qty) as total_qty
                    ')
                    ->first();

                $penggunaanVol = $penggunaanData->total_qty ?? 0;
                
                if ($penggunaanVol > 0) {
                    $penggunaanHarga = $jumlahSampaiHarga;
                    $penggunaanTotal = $penggunaanVol * $penggunaanHarga;
                } else {
                    $penggunaanHarga = 0;
                    $penggunaanTotal = 0;
                }

                /* ================= 5. HARGA PEMBELIAN TERAKHIR ================= */
                $lastPurchasePrice = PurchaseItem::where('item_id', $item->id)
                    ->whereHas('purchase', fn ($q) =>
                        $q->where('period_id', $periode->id)
                          ->whereDate('purchase_date', '<=', $tanggalAkhir)
                    )
                    ->orderByDesc('id')
                    ->value('unit_price');

                /* ================= 6. SISA ================= */
                $sisaPerVol = $jumlahSampaiVol - $penggunaanVol;
                
                if ($sisaPerVol > 0) {
                    $sisaPerHarga = $lastPurchasePrice ?? $saldoAkhirHarga;
                    $hargaPembelianTerakhir = $lastPurchasePrice ?? $saldoAkhirHarga;
                    $sisaPerTotal = $sisaPerVol * $sisaPerHarga;
                } else {
                    $sisaPerHarga = 0;
                    $hargaPembelianTerakhir = 0;
                    $sisaPerTotal = 0;
                }

                /* ================= ITEM DATA ================= */
                $itemData = [
                    'name' => $item->name,
                    'satuan' => $item->itemType->name ?? '-',

                    'saldo_akhir' => [
                        'vol' => $saldoAkhirVol,
                        'harga' => $saldoAkhirHarga,
                        'total' => $saldoAkhirTotal,
                    ],

                    'pengadaan' => [
                        'vol' => $pengadaanVol,
                        'harga' => $pengadaanHarga,
                        'total' => $pengadaanTotal,
                    ],

                    'jumlah_sampai' => [
                        'vol' => $jumlahSampaiVol,
                        'harga' => $jumlahSampaiHarga,
                        'total' => $jumlahSampaiTotal,
                    ],

                    'jumlah_penggunaan' => [
                        'vol' => $penggunaanVol,
                        'harga' => $penggunaanHarga,
                        'total' => $penggunaanTotal,
                    ],

                    'sisa_per' => [
                        'vol' => $sisaPerVol,
                        'harga' => $sisaPerHarga,
                        'harga_pembelian_terakhir' => $hargaPembelianTerakhir,
                        'total' => $sisaPerTotal,
                    ],
                ];

                /* ================= TOTAL PER KATEGORI ================= */
                foreach ([
                    'saldo_akhir',
                    'pengadaan',
                    'jumlah_sampai',
                    'jumlah_penggunaan',
                    'sisa_per'
                ] as $key) {
                    $categoryData['totals'][$key . '_vol']   += $itemData[$key]['vol'];
                    $categoryData['totals'][$key . '_total'] += $itemData[$key]['total'];
                }

                $categoryData['items'][] = $itemData;
            }

            if (!empty($categoryData['items'])) {
                $reportData[] = $categoryData;
            }
        }

        /* ================= TANGGAL SALDO AKHIR ================= */
        $periodeBefore = Period::where('year', '<', $periode->year)
            ->orderByDesc('year')
            ->first();
        
        $tanggalSaldoAkhir = $periodeBefore 
            ? '31-12-' . $periodeBefore->year 
            : '31-12-' . ($periode->year - 1);

        /* ================= VIEW DATA ================= */
        $data = [
            'reportData' => $reportData,
            'tanggalAkhir' => $tanggalAkhirCarbon->format('d-m-Y'),
            'tanggalSisaPer' => $tanggalAkhirCarbon->format('d-m-Y'),
            'tanggalSaldoAkhir' => $tanggalSaldoAkhir,
            'periode' => $periode,
            'generatedAt' => now()->format('d F Y H:i:s'),
        ];

        return Pdf::loadView('report.mutasi-barang', $data)
            ->setPaper('a4', 'landscape')
            ->stream('Laporan-Mutasi-Barang-Periode-' . $periode->year . '-' . $tanggalAkhir . '.pdf');
    }
}