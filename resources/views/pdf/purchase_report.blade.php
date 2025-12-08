{{-- resources/views/pdf/purchase_report.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Data Pembelian</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Catatan</th>
                <th>Total Jumlah</th>
                <th>Items Dibeli</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->note }}</td>
                <td>Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                <td>
                    @foreach ($purchase->purchaseItems as $item)
                        - {{ $item->item->name ?? 'N/A' }} ({{ $item->qty }}  Rp{{ number_format($item->unit_price, 0, ',', '.') }})<br>
                    @endforeach
                </td>
                <td>{{ $purchase->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">TOTAL KESELURUHAN</td>
                <td>Rp{{ number_format($records->sum('total_amount'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>