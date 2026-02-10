<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 15mm 10mm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9px; 
            line-height: 1.4; 
            color: #333; 
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
        }
        .header h1 { 
            font-size: 14px; 
            text-transform: uppercase; 
            margin: 0; 
        }
        
        .filter-info { 
            margin-bottom: 15px; 
            padding: 8px; 
            border: 1px solid #ccc; 
            background-color: #f9f9f9;
            font-size: 9px;
        }
        .filter-info p { 
            margin: 5px 0; 
        }
        .filter-info strong {
            color: #333;
        }
        .filter-info ul { 
            margin: 5px 0 0 20px; 
            padding: 0; 
        }
        .filter-info li { 
            list-style-type: disc; 
            margin-bottom: 3px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        
        th, td { 
            border: 1px solid #000; 
            padding: 5px; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-align: center; 
            text-transform: uppercase; 
            font-size: 8px; 
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        
        .total-row { 
            background-color: #f8fafc; 
            font-weight: bold; 
        }
        .grand-total { 
            background-color: #cbd5e1; 
            font-weight: bold; 
            font-size: 10px; 
        }
        
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            font-size: 9px;
        }
        .summary p {
            margin: 5px 0;
        }
        .summary strong {
            font-size: 10px;
        }
        
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Laporan Data Barang</h1>
    <p style="margin: 5px 0 0 0;">Tanggal Cetak: {{ $generatedAt }}</p>
</div>

@if (!empty($keteranganFilter))
    <div class="filter-info">
        <strong>Filter yang Diterapkan:</strong>
        <ul>
            @foreach ($keteranganFilter as $keterangan)
                <li>{!! str_replace(['**', '*'], ['<b>', '</b>'], $keterangan) !!}</li>
            @endforeach
        </ul>
    </div>
@else
    <div class="filter-info">
        <p><strong>Tidak ada filter yang diterapkan.</strong> Menampilkan semua data barang.</p>
    </div>
@endif

<table>
    <thead>
        <tr>
            <th style="width: 4%;">No.</th>
            <th style="width: 25%;">Nama Barang</th>
            <th style="width: 15%;">Kategori</th>
            <th style="width: 10%;">Satuan</th>
            <th style="width: 10%;">Stok Awal</th>
            <th style="width: 10%;">Pembelian</th>
            <th style="width: 10%;">Penggunaan</th>
            <th style="width: 10%;">Total Stok</th>
            <th style="width: 12%;">Dibuat Pada</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $no = 1;
            $totalInitialStock = 0;
            $totalPurchased = 0;
            $totalUsed = 0;
            $totalStock = 0;
        @endphp
        
        @forelse ($records as $item)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="text-left">{{ $item->name }}</td>
                <td class="text-center">{{ $item->category->name ?? '-' }}</td>
                <td class="text-center">{{ $item->itemType->name ?? '-' }}</td>
                <td class="text-center">{{ number_format($item->initial_stock, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($item->purchased_qty ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($item->used_qty ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($item->total_stock, 0, ',', '.') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d/m/Y') }}</td>
            </tr>
            @php
                $totalInitialStock += $item->initial_stock;
                $totalPurchased += $item->purchased_qty ?? 0;
                $totalUsed += $item->used_qty ?? 0;
                $totalStock += $item->total_stock;
            @endphp
        @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #999;">
                    Tidak ada data barang yang tersedia
                </td>
            </tr>
        @endforelse
    </tbody>
    
    @if ($records->count() > 0)
        <tfoot>
            <tr class="grand-total">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ number_format($totalInitialStock, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($totalPurchased, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($totalUsed, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($totalStock, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>

@if ($records->count() > 0)
    <div class="summary">
        <p><strong>Ringkasan:</strong></p>
        <p>Total Barang: {{ number_format($records->count(), 0, ',', '.') }} item</p>
        <p>Total Stok Awal: {{ number_format($totalInitialStock, 0, ',', '.') }} unit</p>
        <p>Total Pembelian: {{ number_format($totalPurchased, 0, ',', '.') }} unit</p>
        <p>Total Penggunaan: {{ number_format($totalUsed, 0, ',', '.') }} unit</p>
        <p>Total Stok Saat Ini: {{ number_format($totalStock, 0, ',', '.') }} unit</p>
    </div>
@endif

<div class="footer">
    <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
</div>

</body>
</html>