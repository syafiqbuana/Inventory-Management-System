<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Usage;

class ExportUsageNotePdfController extends Controller
{
        public function print(Usage $usage)
    {
        $usage->load([
            'usageItems.item.category',
            'usageItems.item.itemType',
            'createdBy',
            'period',
        ]);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.nota-permohonan', compact('usage'));
        $pdf->setPaper('a4', 'portrait');

        // Stream PDF langsung ke browser untuk preview
        return $pdf->stream('Nota_Pengambilan_Barang_' . $usage->used_for . '_' . $usage->usage_date . '_' . $usage->used_by . '.pdf');}
}
