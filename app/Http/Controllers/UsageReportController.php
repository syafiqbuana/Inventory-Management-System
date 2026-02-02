<?php

namespace App\Http\Controllers;

use App\Models\Usage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UsageReportController extends Controller
{
    public function export(Request $request)
    {
        // Ambil parameter filter dari request
        $categoryIds = $request->input('categories', []);
        $usedBy = $request->input('used_by');
        $dateRange = $request->input('date_range');

        // Query dasar
        $query = Usage::query()->with(['usageItems.item.category', 'createdBy']);

        // Array untuk menyimpan keterangan filter
        $keteranganFilter = [];

        // 1. Filter Kategori
        if (!empty($categoryIds)) {
            $query->whereHas('usageItems.item', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
            
            $categoryNames = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
            $keteranganFilter[] = 'Filter Kategori: ' . implode(', ', $categoryNames);
        }

        // 2. Filter Pengguna
        if ($usedBy) {
            $query->where('used_by', $usedBy);
            $keteranganFilter[] = 'Filter Pengguna: ' . $usedBy;
        }

        // 3. Filter Rentang Tanggal
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $start = trim($dates[0]);
                $end = trim($dates[1]);

                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

                $query->whereBetween('usage_date', [$startDate, $endDate]);

                $formattedStart = $startDate->translatedFormat('d F Y');
                $formattedEnd = $endDate->translatedFormat('d F Y');

                $keteranganFilter[] = 'Filter Tanggal: Dari <b>' . $formattedStart . '</b> sampai <b>' . $formattedEnd . '</b>';
            }
        }

        // Ambil data
        $records = $query->orderBy('usage_date', 'desc')->get();

        // Generate PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.usage_report', compact('records', 'keteranganFilter'));
        $pdf->setPaper('a4', 'landscape');

        $fileName = 'Laporan_Penggunaan_Barang_' . now()->format('Ymd_His') . '.pdf';

        // Stream PDF ke browser untuk preview
        return $pdf->stream($fileName);
    }
}