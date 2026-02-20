<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\ExportPurchasePdfController;
use App\Http\Controllers\ExportUsageNotePdfController;
use App\Http\Controllers\UsageReportController;
use App\Http\Controllers\ItemReportController;
use App\Http\Controllers\ItemHistoryReportController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/reports/mutasi-barang', [InventoryReportController::class, 'mutasiBarang'])
    ->name('reports.mutasi-barang')
    ->middleware(['auth']);

Route::get('/purchase-report/stream', [ExportPurchasePdfController::class, 'stream'])
    ->name('purchase.report.stream')
    ->middleware(['auth']);

Route::get('/usage/{usage}/print', [ExportUsageNotePdfController::class, 'print'])
    ->name('usage.print')
    ->middleware(['auth']);

Route::get('/usage-report/export', [UsageReportController::class, 'export'])
    ->name('usage.report.export')
    ->middleware('auth');

Route::get('/item-report/export', [ItemReportController::class, 'export'])
    ->name('item.report.export')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/items/{item}/history/report', [ItemHistoryReportController::class, 'stream'])
        ->name('items.history.report');
});

