<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pemasukan</title>
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
        .filter-info p { 
            margin: 5px 0; 
            font-size: 12px; 
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
            font-size: 12px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            page-break-inside: auto;
        }
        
        thead {
            display: table-header-group; /* Repeat header di setiap halaman */
        }
        
        tfoot {
            display: table-footer-group; /* Footer tetap di akhir */
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        th, td { 
            border: 1px solid #333; 
            padding: 8px; 
            text-align: left; 
            font-size: 11px; 
        }
        th { 
            background-color: #e0e0e0; 
            text-align: center;
            font-weight: bold;
            color: #333;
        }
        .right { 
            text-align: right; 
        }
        .center { 
            text-align: center; 
        }
        
        tfoot tr.total {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        tfoot tr.balance {
            background-color: #e8f4f8;
            font-weight: bold;
            border-top: 2px solid #333;
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
        @media print {
            body {
                margin: 0;
            }
            .header, .filter-info {
                page-break-after: avoid;
            }
            .summary, .footer {
                page-break-before: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA PEMASUKAN</h1>
        <p>Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
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
            <p><strong>Tidak ada filter yang diterapkan.</strong> Menampilkan semua data pemasukan.</p>
        </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No.</th>
                <th style="width: 10%;">ID</th>
                <th style="width: 25%;">Sumber Pemasukan</th>
                <th style="width: 22%;">Jumlah Pemasukan</th>
                <th style="width: 20%;">Tanggal Pemasukan</th>
                <th style="width: 15%;">Updated At</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
                $grandTotal = 0;
            @endphp
            
            @forelse ($records as $income)
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td class="center">{{ $income->id }}</td>
                    <td>{{ $income->source ?? '-' }}</td>
                    <td class="right">Rp {{ number_format($income->amount, 0, ',', '.') }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($income->created_at)->translatedFormat('d F Y') }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($income->updated_at)->translatedFormat('d F Y') }}</td>
                </tr>
                @php
                    $grandTotal += $income->amount;
                @endphp
            @empty
                <tr>
                    <td colspan="6" class="center" style="padding: 20px; color: #999;">
                        Tidak ada data pemasukan yang tersedia
                    </td>
                </tr>
            @endforelse
        </tbody>
        
        @if ($records->count() > 0)
            <tfoot>
                {{-- Baris 1: Total Pemasukan Keseluruhan --}}
                <tr class="total">
                    <th colspan="3" class="right">TOTAL PEMASUKAN:</th>
                    <th class="right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                    <th colspan="2"></th>
                </tr>
                
                {{-- Baris 2: SISA SALDO GLOBAL --}}
                <tr class="balance">
                    <th colspan="5" class="right">SISA SALDO GLOBAL:</th>
                    <th class="right">
                        @php
                            $balance = \App\Models\Balance::first();
                            $currentBalance = $balance->amount ?? 0;
                        @endphp
                        Rp {{ number_format($currentBalance, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        @endif
    </table>
    
    @if ($records->count() > 0)
        <div class="summary">
            <p><strong>Ringkasan:</strong></p>
            <p>Total Transaksi Pemasukan: {{ number_format($records->count(), 0, ',', '.') }} transaksi</p>
            <p>Total Nilai Pemasukan: Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            <p>Sisa Saldo Global: Rp {{ number_format($currentBalance ?? 0, 0, ',', '.') }}</p>
        </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
    </div>
</body>
</html>