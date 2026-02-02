<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penggunaan Barang</title>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }

        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }

        .filter-info strong {
            color: #333;
            font-size: 12px;
        }

        .filter-info ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        .filter-info li {
            list-style-type: disc;
            margin-bottom: 3px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }

        th {
            background-color: #e0e0e0;
            text-align: center;
            font-weight: bold;
            color: #333;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            page-break-inside: avoid;
        }

        .summary p {
            margin: 5px 0;
            font-size: 12px;
        }

        .summary strong {
            font-size: 13px;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #666;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>LAPORAN PENGGUNAAN BARANG</h1>
    <p>Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
</div>

@if (!empty($keteranganFilter))
    <div class="filter-info">
        <strong>Filter yang Diterapkan:</strong>
        <ul>
            @foreach ($keteranganFilter as $keterangan)
                <li>{!! $keterangan !!}</li>
            @endforeach
        </ul>
    </div>
@else
    <div class="filter-info">
        <p><strong>Tidak ada filter yang diterapkan.</strong> Menampilkan semua data penggunaan.</p>
    </div>
@endif

<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:15%">Pengguna</th>
            <th style="width:10%">Tanggal</th>
            <th style="width:20%">Digunakan Untuk</th>
            <th style="width:25%">Item</th>
            <th style="width:15%">Kategori</th>
            <th style="width:10%">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $totalTransaksi = 0;
            $totalQty = 0;
        @endphp

        @forelse ($records as $usage)
            @php
                $rowspan = $usage->usageItems->count();
                $totalTransaksi++;
            @endphp

            @foreach ($usage->usageItems as $index => $usageItem)
                @php $totalQty += $usageItem->qty; @endphp
                <tr>
                    @if ($index === 0)
                        <td rowspan="{{ $rowspan }}" class="center">{{ $no++ }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $usage->used_by }}</td>
                        <td rowspan="{{ $rowspan }}" class="center">
                            {{ \Carbon\Carbon::parse($usage->usage_date)->translatedFormat('d F Y') }}
                        </td>
                        <td rowspan="{{ $rowspan }}">{{ $usage->used_for }}</td>
                    @endif

                    <td>{{ $usageItem->item->name ?? '-' }}</td>
                    <td class="center">{{ $usageItem->item->category->name ?? '-' }}</td>
                    <td class="center">{{ $usageItem->qty }} {{ $usageItem->item->type ?? 'unit' }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="7" class="center" style="padding:20px;color:#999;">
                    Tidak ada data penggunaan barang
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($records->count() > 0)
    <div class="summary">
        <p><strong>Ringkasan:</strong></p>
        <p>Total Transaksi Penggunaan: {{ $totalTransaksi }} transaksi</p>
        <p>Total Jumlah Digunakan: {{ number_format($totalQty, 0, ',', '.') }} unit</p>
    </div>
@endif

<div class="footer">
    <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
</div>

</body>
</html>
    