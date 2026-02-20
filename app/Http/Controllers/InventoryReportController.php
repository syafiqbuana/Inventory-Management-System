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

        $periode = Period::find($periodeId);

        if (!$periode) {
            abort(404, 'Periode tidak ditemukan');
        }

        $categories = Category::with([
            'items' => fn($q) => $q->orderBy('id')
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
                if ($periode->is_closed) {
                    $periodStock = PeriodStock::where('period_id', $periode->id)
                        ->where('item_id', $item->id)
                        ->first();

                    $saldoAkhirVol = $periodStock->initial_stock ?? 0;
                    $saldoAkhirHarga = (float) ($periodStock->price ?? 0); // Cast ke float
                } else {
                    $saldoAkhirVol = ($item->initial_period_id == $periode->id)
                        ? $item->initial_stock
                        : 0;
                    $saldoAkhirHarga = (float) $item->price; // Cast ke float
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
                    ->whereHas(
                        'purchase',
                        fn($q) =>
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
                    $pengadaanTotal = (float) ($pengadaanData->total_amount ?? 0); // Cast ke float
                    $pengadaanHarga = $pengadaanTotal / $pengadaanVol; // Jangan round, biarkan desimal
                } else {
                    $pengadaanHarga = 0;
                    $pengadaanTotal = 0;
                }

                /* ================= 3. JUMLAH SAMPAI ================= */
                $jumlahSampaiVol = $saldoAkhirVol + $pengadaanVol;

                if ($jumlahSampaiVol > 0) {
                    $jumlahSampaiTotal = $saldoAkhirTotal + $pengadaanTotal;
                    $jumlahSampaiHarga = $jumlahSampaiTotal / $jumlahSampaiVol; // Jangan round
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
                    ->whereHas(
                        'purchase',
                        fn($q) =>
                        $q->where('period_id', $periode->id)
                            ->whereDate('purchase_date', '<=', $tanggalAkhir)
                    )
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->orderByDesc('purchases.purchase_date')
                    ->orderByDesc('purchase_items.id')
                    ->value('purchase_items.unit_price');

                $lastPurchasePrice = (float) ($lastPurchasePrice ?? 0); // Cast ke float

                /* ================= 6. SISA ================= */
                $sisaPerVol = $jumlahSampaiVol - $penggunaanVol;

                if ($sisaPerVol > 0) {
                    $sisaPerHarga = $lastPurchasePrice ?: $saldoAkhirHarga;
                    $hargaPembelianTerakhir = $lastPurchasePrice ?: $saldoAkhirHarga;
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

                // Skip barang yang semua volume-nya 0 (habis terpakai/tidak ada stok)
                if ($this->isItemEmpty($itemData)) {
                    continue;
                }

                /* ================= TOTAL PER KATEGORI ================= */
                foreach ([
                    'saldo_akhir',
                    'pengadaan',
                    'jumlah_sampai',
                    'jumlah_penggunaan',
                    'sisa_per'
                ] as $key) {
                    $categoryData['totals'][$key . '_vol'] += $itemData[$key]['vol'];
                    $categoryData['totals'][$key . '_total'] += $itemData[$key]['total'];
                }

                $categoryData['items'][] = $itemData;
            }

            if (!empty($categoryData['items'])) {
                $reportData[] = $categoryData;
            }
        }

        /* ================= RINGKASAN PER KATEGORI ================= */
        $summaryData = [];

        foreach ($reportData as $category) {
            $summaryData[] = [
                'name' => $category['name'],
                'saldo_akhir_vol' => $category['totals']['saldo_akhir_vol'],
                'saldo_akhir_total' => $category['totals']['saldo_akhir_total'],
                'pengadaan_vol' => $category['totals']['pengadaan_vol'],
                'pengadaan_total' => $category['totals']['pengadaan_total'],
                'jumlah_sampai_vol' => $category['totals']['jumlah_sampai_vol'],
                'jumlah_sampai_total' => $category['totals']['jumlah_sampai_total'],
                'jumlah_penggunaan_vol' => $category['totals']['jumlah_penggunaan_vol'],
                'jumlah_penggunaan_total' => $category['totals']['jumlah_penggunaan_total'],
                'sisa_per_vol' => $category['totals']['sisa_per_vol'],
                'sisa_per_total' => $category['totals']['sisa_per_total'],
            ];
        }

        /* ================= GRAND TOTAL ================= */
        $grandTotal = [
            'saldo_akhir_vol' => array_sum(array_column($summaryData, 'saldo_akhir_vol')),
            'saldo_akhir_total' => array_sum(array_column($summaryData, 'saldo_akhir_total')),
            'pengadaan_vol' => array_sum(array_column($summaryData, 'pengadaan_vol')),
            'pengadaan_total' => array_sum(array_column($summaryData, 'pengadaan_total')),
            'jumlah_sampai_vol' => array_sum(array_column($summaryData, 'jumlah_sampai_vol')),
            'jumlah_sampai_total' => array_sum(array_column($summaryData, 'jumlah_sampai_total')),
            'jumlah_penggunaan_vol' => array_sum(array_column($summaryData, 'jumlah_penggunaan_vol')),
            'jumlah_penggunaan_total' => array_sum(array_column($summaryData, 'jumlah_penggunaan_total')),
            'sisa_per_vol' => array_sum(array_column($summaryData, 'sisa_per_vol')),
            'sisa_per_total' => array_sum(array_column($summaryData, 'sisa_per_total')),
        ];

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
            'summaryData' => $summaryData,     // TAMBAHAN BARU
            'grandTotal' => $grandTotal,
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

    /**
     * 
     * 
     * @param array $itemData
     * @return bool
     */
    private function isItemEmpty(array $itemData): bool
    {
        return $itemData['saldo_akhir']['vol'] == 0
            && $itemData['pengadaan']['vol'] == 0
            && $itemData['jumlah_sampai']['vol'] == 0
            && $itemData['jumlah_penggunaan']['vol'] == 0
            && $itemData['sisa_per']['vol'] == 0;
    }
}