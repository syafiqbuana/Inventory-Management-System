{{-- resources/views/filament/resources/item-resource/report/item-history-report.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Barang - {{ $item->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
        }

        /* TABLE DATA */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table thead {
            background-color: #d9d9d9;
        }

        table.data-table th {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
            padding-right: 5px;
        }

        .text-left {
            text-align: left;
        }

        .row-saldo-awal {
            background-color: #fff9e6;
        }

        .row-pembelian {
            background-color: #e6f3ff;
        }

        .row-penggunaan {
            background-color: #ffe6e6;
        }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .footer-section {
            width: 40%;
            text-align: center;
            font-size: 9px;
        }

        .footer-signature {
            margin-top: 30px;
            font-weight: bold;
        }

        .empty-message {
            text-align: center;
            padding: 15px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>

<body>

    @php
        function fmtNum($n)
        {
            if ($n == 0 || $n === null) {
                return '-';
            }
            return floor($n) == $n ? number_format($n, 0, ',', '.') : number_format($n, 2, ',', '.');
        }
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <h1>KARTU BARANG</h1>

        <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
            <tr>
                <td
                    style="border: 1px solid #000; padding: 5px; width: 20%; font-weight: bold; background-color: #f5f5f5;">
                    JENIS BARANG</td>
                <td style="border: 1px solid #000; padding: 5px; width: 30%;"> : {{ $item->category?->name ?? '-' }}</td>
                <td
                    style="border: 1px solid #000; padding: 5px; width: 20%; text-align: center; font-weight: bold; background-color: #f5f5f5;">
                    STOK AKHIR</td>
                <td
                    style="border: 1px solid #000; padding: 5px; width: 30%; text-align: center; font-weight: bold; font-size: 12px;">
                    {{ number_format($finalStock, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td
                    style="border: 1px solid #000; padding: 5px; width: 20%; font-weight: bold; background-color: #f5f5f5;">
                    NAMA BARANG</td>
                <td style="border: 1px solid #000; padding: 5px; width: 30%;"> : {{ $item->name }}</td>
                <td style="border: 1px solid #000; padding: 5px; width: 20%;"></td>
                <td style="border: 1px solid #000; padding: 5px; width: 30%;"></td>
            </tr>
            <tr>
                <td
                    style="border: 1px solid #000; padding: 5px; width: 20%; font-weight: bold; background-color: #f5f5f5;">
                    SATUAN</td>
                <td style="border: 1px solid #000; padding: 5px; width: 30%;"> {{ $satuan }}</td>
                <td style="border: 1px solid #000; padding: 5px; width: 20%;"></td>
                <td style="border: 1px solid #000; padding: 5px; width: 30%;"></td>
            </tr>
        </table>
    </div>

    {{-- DATA TABLE --}}
    @if ($transactions->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%">No.</th>
                    <th style="width: 10%">TANGGAL</th>
                    <th style="width: 15%">Nomor SBBM/SBBK</th>
                    <th style="width: 20%">ALAMAT TUJUAN</th>
                    <th style="width: 8%">JUMLAH MASUK</th>
                    <th style="width: 8%">JUMLAH KELUAR</th>
                    <th style="width: 8%">SISA</th>
                    <th style="width: 12%">HARGA SATUAN</th>
                    <th style="width: 16%">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $i => $trans)
                    <tr class="row-{{ $trans['type'] }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">
                            @if ($trans['date'])
                                {{ \Carbon\Carbon::parse($trans['date'])->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $trans['reference'] }}</td>
                        <td class="text-left">{{ $trans['purpose'] ?? '-' }}</td>
                        <td class="text-center">
                            @if ($trans['qty_in'] > 0)
                                {{ $trans['qty_in'] }}
                            @else
                                - <!-- ← Kolom tetap ada, tapi isi dengan - -->
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($trans['qty_out'] > 0)
                                {{ $trans['qty_out'] }}
                            @endif
                        </td>
                        <td class="text-center">
                            <strong>{{ number_format($trans['sisa'], 0, ',', '.') }}</strong>
                        </td>
                        <td class="text-right">
                            @if ($trans['unit_price'] > 0)
                                Rp {{ fmtNum($trans['unit_price']) }}
                            @endif
                        </td>
                        <td class="text-left">{{ $trans['notes'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-message">
            Belum ada transaksi untuk barang ini
        </div>
    @endif

    {{-- SIGNATURE SECTION --}}
    <div class="footer">
        <div class="footer-section">
            <div>Purwokerto, {{ now()->translatedFormat('d F Y') }}</div>
            <div class="footer-signature">
                pengurus barang
            </div>
            <div style="margin-top: 40px;">
                _____________________________
            </div>
            <div style="font-size: 8px; margin-top: 5px;">
                NIP. ____________________
            </div>
        </div>
    </div>

</body>

</html>
