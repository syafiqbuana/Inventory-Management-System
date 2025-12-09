<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; } 
        .total { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Data Pembelian</h1>

    <table>
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">Catatan</th>
                <th class="text-center">Total Jumlah</th>
                <th class="text-center">Items Dibeli</th>
                <th class="text-center">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->note }}</td>
                <td class="text-right">Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                <td>
                    @foreach ($purchase->purchaseItems as $item)
                        - {{ $item->item->name ?? 'N/A' }} ({{ $item->qty }} @ Rp{{ number_format($item->unit_price, 0, ',', '.') }})<br>
                    @endforeach
                </td>
                <td class="text-center">{{ $purchase->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            {{-- Baris 1: Total Pembelian Keseluruhan --}}
            <tr class="total">
                <td colspan="2">TOTAL TRANSAKSI INI</td>
                <td class="text-right">Rp{{ number_format($records->sum('total_amount'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            
            {{-- Baris 2: SISA SALDO GLOBAL --}}
            <tr class="total">
                <td colspan="4" class="text-right" style="border-right: none;">SISA SALDO GLOBAL</td>
                <td class="text-right">
                    @php
                        // Memanggil model Balance secara langsung karena saldo bersifat global (diasumsikan ID=1)
                        $balance = \App\Models\Balance::find(1);
                        $currentBalance = $balance->amount ?? 0;
                    @endphp
                    Rp{{ number_format($currentBalance, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>