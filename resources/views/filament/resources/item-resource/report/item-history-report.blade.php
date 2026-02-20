{{-- resources/views/filament/resources/item-resource/report/item-history-report.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Barang - {{ $item->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 12mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #000;
        }

        /* HEADER */
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.5;
        }
        .header .sub {
            font-size: 8.5px;
            margin-top: 4px;
            color: #333;
        }

        /* DIVIDER */
        .divider { border-top: 2px solid #000; margin: 6px 0; }
        .divider-thin { border-top: 1px solid #aaa; margin: 10px 0; }

        /* INFO BARANG */
        .info-table { width: 100%; margin-bottom: 12px; }
        .info-table td { font-size: 8px; padding: 2px 4px; }
        .info-table .label { width: 20%; font-weight: bold; }
        .info-table .colon { width: 1%; }

        /* SUMMARY CARDS */
        .summary-wrap { width: 100%; margin-bottom: 14px; }
        .summary-wrap table { width: 100%; border-collapse: collapse; }
        .summary-wrap td {
            width: 25%;
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
            background-color: #f2f2f2;
        }
        .summary-wrap td.highlight {
            background-color: #d9ead3;
        }
        .summary-wrap .card-label { font-size: 7px; color: #555; }
        .summary-wrap .card-value { font-size: 11px; font-weight: bold; color: #000; margin-top: 2px; }

        /* SECTION TITLE */
        .section-title {
            background-color: #d9d9d9;
            font-weight: bold;
            font-size: 8.5px;
            padding: 3px 5px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* TABLE */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data-table th {
            background-color: #d9d9d9;
            font-size: 7px;
            font-weight: bold;
            padding: 3px 3px;
            border: 1px solid #000;
            text-align: center;
        }
        table.data-table td {
            font-size: 7.5px;
            padding: 2px 3px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }

        .total-row { background-color: #e6e6e6; font-weight: bold; }
        .empty-row td { text-align: center; color: #888; font-style: italic; padding: 6px; }

        /* FOOTER */
        .footer { margin-top: 10px; font-size: 7px; color: #555; text-align: right; }
    </style>
</head>
<body>

@php
    function fmtNum($n) {
        if ($n == 0) return '-';
        return floor($n) == $n
            ? number_format($n, 0, ',', '.')
            : number_format($n, 2, ',', '.');
    }
    $satuan = $item->itemType?->name ?? 'unit';
@endphp

{{-- HEADER --}}
<div class="header">
    <h1>
        LAPORAN RIWAYAT BARANG PERSEDIAAN<br>
        {{ strtoupper($item->name) }}
    </h1>
    <div class="sub">
        Kategori: {{ $item->category?->name ?? '-' }} &nbsp;|&nbsp;
        Satuan: {{ $satuan }}
    </div>
</div>
<div class="divider"></div>

{{-- INFO BARANG --}}
<table class="info-table">
    <tr>
        <td class="label">Nama Barang</td>
        <td class="colon">:</td>
        <td>{{ $item->name }}</td>
        <td class="label">Kategori</td>
        <td class="colon">:</td>
        <td>{{ $item->category?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Stok Awal</td>
        <td class="colon">:</td>
        <td>{{ $item->initial_stock }} {{ $satuan }}</td>
        <td class="label">Harga Satuan</td>
        <td class="colon">:</td>
        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Stok Saat Ini</td>
        <td class="colon">:</td>
        <td><strong>{{ $currentStock }} {{ $satuan }}</strong></td>
        <td class="label">Nilai Pembelian</td>
        <td class="colon">:</td>
        <td>Rp {{ number_format($totalNilaiPembelian, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Tanggal Input</td>
        <td class="colon">:</td>
        <td>{{ $item->created_at?->translatedFormat('d F Y') }}</td>
        <td class="label">Satuan</td>
        <td class="colon">:</td>
        <td>{{ $satuan }}</td>
    </tr>
</table>

{{-- SUMMARY CARDS --}}
<div class="summary-wrap">
    <table>
        <tr>
            <td>
                <div class="card-label">Stok Awal</div>
                <div class="card-value">{{ number_format($item->initial_stock, 0, ',', '.') }} {{ $satuan }}</div>
            </td>
            <td>
                <div class="card-label">Total Pembelian</div>
                <div class="card-value">{{ number_format($totalPembelian, 0, ',', '.') }} {{ $satuan }}</div>
            </td>
            <td>
                <div class="card-label">Total Penggunaan</div>
                <div class="card-value">{{ number_format($totalPenggunaan, 0, ',', '.') }} {{ $satuan }}</div>
            </td>
            <td class="highlight">
                <div class="card-label">Stok Saat Ini</div>
                <div class="card-value">{{ number_format($currentStock, 0, ',', '.') }} {{ $satuan }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="divider-thin"></div>

{{-- RIWAYAT PEMBELIAN --}}
<div class="section-title">Riwayat Pembelian</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:14%">Tanggal</th>
            <th style="width:8%">Jumlah</th>
            <th style="width:14%">Harga Satuan</th>
            <th style="width:14%">Subtotal</th>
            <th style="width:20%">Supplier</th>
            <th style="width:14%">Periode</th>
            <th style="width:12%">Diinput Oleh</th>
        </tr>
    </thead>
    <tbody>
        @forelse($item->purchaseItems as $i => $pi)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($pi->purchase?->purchase_date)->format('d/m/Y') }}</td>
            <td class="text-center">{{ $pi->qty }} {{ $satuan }}</td>
            <td class="text-right">Rp {{ fmtNum($pi->unit_price) }}</td>
            <td class="text-right">Rp {{ fmtNum($pi->subtotal) }}</td>
            <td>{{ $pi->supplier }}</td>
            <td class="text-center">{{ $pi->purchase?->period?->year }}</td>
            <td class="text-center">{{ $pi->purchase?->createdBy?->name ?? '-' }}</td>
        </tr>
        @empty
        <tr class="empty-row"><td colspan="8">Belum ada riwayat pembelian</td></tr>
        @endforelse
        @if($item->purchaseItems->count() > 0)
        <tr class="total-row">
            <td colspan="2" class="text-center">TOTAL</td>
            <td class="text-center">{{ $totalPembelian }} {{ $satuan }}</td>
            <td></td>
            <td class="text-right">Rp {{ fmtNum($totalNilaiPembelian) }}</td>
            <td colspan="3"></td>
        </tr>
        @endif
    </tbody>
</table>

{{-- RIWAYAT PENGGUNAAN --}}
<div class="section-title">Riwayat Penggunaan</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:14%">Tanggal</th>
            <th style="width:8%">Jumlah</th>
            <th style="width:20%">Digunakan Untuk</th>
            <th style="width:20%">Diambil Oleh</th>
            <th style="width:14%">Periode</th>
            <th style="width:20%">Diinput Oleh</th>
        </tr>
    </thead>
    <tbody>
        @forelse($item->usageItems as $i => $ui)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($ui->usage?->usage_date)->format('d/m/Y') }}</td>
            <td class="text-center">{{ $ui->qty }} {{ $satuan }}</td>
            <td>{{ $ui->usage?->used_for }}</td>
            <td>{{ $ui->usage?->used_by }}</td>
            <td class="text-center">{{ $ui->usage?->period?->year }}</td>
            <td class="text-center">{{ $ui->usage?->createdBy?->name ?? '-' }}</td>
        </tr>
        @empty
        <tr class="empty-row"><td colspan="7">Belum ada riwayat penggunaan</td></tr>
        @endforelse
        @if($item->usageItems->count() > 0)
        <tr class="total-row">
            <td colspan="2" class="text-center">TOTAL</td>
            <td class="text-center">{{ $totalPenggunaan }} {{ $satuan }}</td>
            <td colspan="4"></td>
        </tr>
        @endif
    </tbody>
</table>

{{-- STOCK PER PERIODE --}}
<div class="section-title">Stock Per Periode</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:20%">Periode/Tahun</th>
            <th style="width:20%">Stok Awal</th>
            <th style="width:20%">Stok Akhir</th>
            <th style="width:36%">Harga Saat Itu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($item->periodStocks as $i => $ps)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $ps->period?->year }}</td>
            <td class="text-center">{{ $ps->initial_stock }} {{ $satuan }}</td>
            <td class="text-center">{{ $ps->final_stock }} {{ $satuan }}</td>
            <td class="text-right">Rp {{ fmtNum($ps->price) }}</td>
        </tr>
        @empty
        <tr class="empty-row"><td colspan="5">Belum ada data stock periode</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dicetak pada: {{ $generatedAt }}
</div>

</body>
</html>