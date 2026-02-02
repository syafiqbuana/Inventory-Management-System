<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 15mm 10mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 14px; text-transform: uppercase; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 8px; }
        .category-header { background-color: #e2e8f0; font-weight: bold; font-size: 10px; padding: 6px; }
        .total-row { background-color: #f8fafc; font-weight: bold; }
        .grand-total { background-color: #cbd5e1; font-weight: bold; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>Laporan Detail Pengadaan Barang Per Kategori</h1>
    <p style="margin: 5px 0 0 0;">Tanggal Cetak: {{ $generatedAt }}</p>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 10%;">Tanggal</th>
            <th style="width: 25%;">Nama Barang</th>
            <th style="width: 25%;">Supplier & Catatan</th>
            <th style="width: 8%;">Jumlah</th>
            <th style="width: 13%;">Harga Satuan</th>
            <th style="width: 15%;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach ($reportData as $categoryName => $data)
            <tr>
                <td colspan="7" class="category-header">KATEGORI: {{ strtoupper($categoryName) }}</td>
            </tr>
            @foreach ($data['items'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                    <td>{{ $item['item_name'] }}</td>
                    <td>
                        <strong>{{ $item['supplier'] }}</strong><br>
                        <small style="color: #666;">{{ $item['note'] }}</small>
                    </td>
                    <td class="text-center">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">Subtotal Kategori {{ $categoryName }}:</td>
                <td class="text-right">Rp {{ number_format($data['total_category_amount'], 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $data['total_category_amount']; @endphp
        @endforeach
    </tbody>
    <tfoot>
        <tr class="grand-total">
            <td colspan="6" class="text-right">TOTAL KESELURUHAN (SEMUA KATEGORI):</td>
            <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>