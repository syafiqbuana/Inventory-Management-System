<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemHistoryReportController extends Controller
{
public function stream(Item $item)
{
    $item->load([
        'category',
        'itemType',
        'purchaseItems.purchase.period',
        'purchaseItems.purchase.createdBy',
        'usageItems.usage.period',
        'usageItems.usage.createdBy',
        'periodStocks.period',
    ]);

    $activePeriod = \App\Models\Period::active();

    // Hitung manual seperti yang dilakukan stockForPeriod()
    $totalPembelian = $item->purchaseItems
        ->filter(fn($pi) => $pi->purchase?->period_id === $activePeriod?->id)
        ->sum('qty');

    $totalPenggunaan = $item->usageItems
        ->filter(fn($ui) => $ui->usage?->period_id === $activePeriod?->id)
        ->sum('qty');

    $totalNilaiPembelian = $item->purchaseItems->sum('subtotal');

    $initialStock = (int) $item->initial_period_id === $activePeriod?->id
        ? $item->initial_stock
        : 0;

    $currentStock = $initialStock + $totalPembelian - $totalPenggunaan;

    $pdf = Pdf::loadView(
        'filament.resources.item-resource.report.item-history-report',
        [
            'item'                => $item,
            'totalPembelian'      => $totalPembelian,
            'totalPenggunaan'     => $totalPenggunaan,
            'totalNilaiPembelian' => $totalNilaiPembelian,
            'currentStock'        => $currentStock,
            'generatedAt'         => now()->translatedFormat('d F Y, H:i'),
        ]
    )->setPaper('a4', 'portrait');

    return $pdf->stream("riwayat-{$item->id}-{$item->name}.pdf");
}
}