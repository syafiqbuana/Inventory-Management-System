<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\ExportPurchasePdfController;
use App\Http\Controllers\ExportUsageNotePdfController;
use App\Http\Controllers\UsageReportController;

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
