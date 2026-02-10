<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\App;

class ExportPurchasePdfController extends Controller
{
    public function stream(Request $request) {
        $ids = explode(',', $request->query('ids'));

        $purchases = Purchase::with(['purchaseItems.item.category'])
            ->whereIn('id', $ids)
            ->get();

        $groupedData = [];
        foreach ($purchases as $purchase) {
            foreach ($purchase->purchaseItems as $pItem) {
                $categoryName = $pItem->item->category->name ?? 'TANPA KATEGORI';
                
                if (!isset($groupedData[$categoryName])) {
                    $groupedData[$categoryName] = [
                        'name' => $categoryName,
                        'items' => [],
                        'total_category_amount' => 0,
                    ];
                }

                $groupedData[$categoryName]['items'][] = [
                    'date' => $purchase->purchase_date ?? $purchase->created_at,
                    'note' => $purchase->note,
                    'item_name' => $pItem->item->name ?? 'N/A',
                    'qty' => $pItem->qty,
                    'unit_price' => $pItem->unit_price,
                    'subtotal' => $pItem->qty * $pItem->unit_price,
                    'supplier' => $pItem->supplier,
                ];

                $groupedData[$categoryName]['total_category_amount'] += ($pItem->qty * $pItem->unit_price);
            }
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.purchase_report', [
            'reportData' => $groupedData,
            'generatedAt' => now()->translatedFormat('d F Y H:i:s'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Pembelian.pdf');
    }
}
