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

        $categoryIds = $request->input('categories', []);
        $usedBy = $request->input('used_by');
        $dateRange = $request->input('date_range');

        $query = Usage::query()->with(['usageItems.item.category', 'createdBy']);

        $keteranganFilter = [];

        if (!empty($categoryIds)) {
            $query->whereHas('usageItems.item', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
            
            $categoryNames = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
            $keteranganFilter[] = 'Filter Kategori: ' . implode(', ', $categoryNames);
        }

        if ($usedBy) {
            $query->where('used_by', $usedBy);
            $keteranganFilter[] = 'Filter Pengguna: ' . $usedBy;
        }

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

        $records = $query->orderBy('usage_date', 'desc')->get();
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.usage_report', compact('records', 'keteranganFilter'));
        $pdf->setPaper('a4', 'landscape');
        $fileName = 'Laporan_Penggunaan_Barang_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }
}