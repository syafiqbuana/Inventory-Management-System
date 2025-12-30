<!DOCTYPE html>
<html>
<head>
    <title>Laporan Saldo</title>
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
        <h1>LAPORAN SALDO KEUANGAN</h1>
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
            <p><strong>Tidak ada filter yang diterapkan.</strong> Menampilkan data bulan berjalan.</p>
        </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 28%;">Keterangan</th>
                <th style="width: 15%;">Pemasukan (+)</th>
                <th style="width: 15%;">Pengeluaran (-)</th>
                <th style="width: 15%;">Saldo Berjalan</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
                $runningSaldo = $initialBalance;
            @endphp
            
            @forelse ($transactions as $transaction)
                @php
                    if ($transaction->type === 'income') {
                        $runningSaldo += $transaction->amount;
                    } else {
                        $runningSaldo -= $transaction->amount;
                    }
                @endphp
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d F Y') }}</td>
                    <td class="center">
                        @if($transaction->type === 'income')
                            <strong style="color: #28a745;">MASUK</strong>
                        @else
                            <strong style="color: #dc3545;">KELUAR</strong>
                        @endif
                    </td>
                    <td>
                        @if($transaction->type === 'income')
                            {{ $transaction->source ?? '-' }}
                        @else
                            {{ $transaction->note ?? '-' }}
                        @endif
                    </td>
                    <td class="right">
                        @if($transaction->type === 'income')
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="right">
                        @if($transaction->type === 'purchase')
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="right"><strong>Rp {{ number_format($runningSaldo, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center" style="padding: 20px; color: #999;">
                        Tidak ada transaksi dalam periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
        
        @if(count($transactions) > 0)
            <tfoot>
                {{-- Baris 1: Total Transaksi --}}
                <tr class="total">
                    <th colspan="4" class="right">TOTAL TRANSAKSI:</th>
                    <th class="right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</th>
                    <th class="right">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
                
                {{-- Baris 2: Saldo Akhir --}}
                <tr class="balance">
                    <th colspan="6" class="right">SALDO AKHIR:</th>
                    <th class="right">Rp {{ number_format($balance->amount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
    
    @if(count($transactions) > 0)
        <div class="summary">
            <p><strong>Ringkasan:</strong></p>
            <p>Saldo Awal Periode: Rp {{ number_format($initialBalance, 0, ',', '.') }}</p>
            <p>Total Pemasukan: {{ number_format($incomeCount, 0, ',', '.') }} transaksi senilai Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
            <p>Total Pengeluaran: {{ number_format($purchaseCount, 0, ',', '.') }} transaksi senilai Rp {{ number_format($totalPurchase, 0, ',', '.') }}</p>
            <p>Perubahan Bersih: 
                <span style="color: {{ ($totalIncome - $totalPurchase) >= 0 ? '#28a745' : '#dc3545' }};">
                    {{ ($totalIncome - $totalPurchase) >= 0 ? '+' : '' }}Rp {{ number_format($totalIncome - $totalPurchase, 0, ',', '.') }}
                </span>
            </p>
            <p>Saldo Akhir Periode: Rp {{ number_format($balance->amount, 0, ',', '.') }}</p>
        </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
    </div>
</body>
</html>