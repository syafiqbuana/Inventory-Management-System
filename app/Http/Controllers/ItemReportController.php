<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ItemReportController extends Controller
{
    public function export(Request $request)
    {
        // Ambil parameter filter dari request
        $categoryId = $request->input('category');
        $dateRange = $request->input('date_range');

        // Dapatkan active period
        $activePeriod = Period::active();

        // Query dasar dengan semua relasi dan agregat yang dibutuhkan
        $query = Item::query()
            ->with([
                'category',
                'itemType',
                'initialPeriod',
                'createdBy'
            ])
            // Tambahkan agregat untuk purchased_qty
            ->withSum([
                'purchaseItems as purchased_qty' => function ($query) use ($activePeriod) {
                    if ($activePeriod) {
                        $query->whereHas('purchase', function ($q) use ($activePeriod) {
                            $q->where('period_id', '<=', $activePeriod->id);
                        });
                    }
                }
            ], 'qty')
            // Tambahkan agregat untuk used_qty
            ->withSum([
                'usageItems as used_qty' => function ($query) use ($activePeriod) {
                    if ($activePeriod) {
                        $query->whereHas('usage', function ($q) use ($activePeriod) {
                            $q->where('period_id', '<=', $activePeriod->id);
                        });
                    }
                }
            ], 'qty');

        // Array untuk menyimpan keterangan filter
        $keteranganFilter = [];

        // 1. Filter Kategori
        if ($categoryId) {
            $query->where('category_id', $categoryId);
            
            $categoryName = Category::find($categoryId)->name ?? 'Tidak Ditemukan';
            $keteranganFilter[] = 'Filter Kategori: ' . $categoryName;
        }

        // 2. Filter Rentang Tanggal
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $start = trim($dates[0]);
                $end = trim($dates[1]);

                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);

                $formattedStart = $startDate->translatedFormat('d F Y');
                $formattedEnd = $endDate->translatedFormat('d F Y');

                $keteranganFilter[] = 'Filter Tanggal Dibuat: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
            }
        }

        // Ambil data dengan urutan yang sama seperti di tabel
        $records = $query->orderBy('created_at', 'desc')->get();

        // Generate PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.item_report', compact('records', 'keteranganFilter'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Data_Barang_' . now()->format('Ymd_His') . '.pdf';

        // Stream PDF ke browser untuk preview
        return $pdf->stream($fileName);
    }
}