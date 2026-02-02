<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Barang - Periode {{ $periode->year }}</title>
    <style>
        /* ================= PAGE SETUP ================= */
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
        }

        /* ================= HEADER ================= */
        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .header .periode-info {
            font-size: 9px;
            font-weight: bold;
            margin-top: 5px;
            color: #333;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: middle;
        }

        th {
            font-size: 7px;
            font-weight: bold;
            background-color: #d9d9d9;
            text-align: center;
        }

        td {
            font-size: 7.5px;
        }

        /* ================= ALIGNMENT ================= */
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* ================= COLORS ================= */
        .bg-gray {
            background-color: #d9d9d9;
        }

        .category-header {
            background-color: #bfbfbf;
            font-weight: bold;
            font-size: 8px;
            padding: 3px 5px;
            text-align: left;
        }

        .total-row {
            background-color: #e6e6e6;
            font-weight: bold;
            font-size: 7.5px;
        }

        /* ================= WIDTHS ================= */
        .col-no { width: 2.5%; }
        .col-nama { width: 14%; }
        .col-satuan { width: 3.5%; }
        .col-vol { width: 3%; }
        .col-harga { width: 5%; }
        .col-total { width: 5.5%; }
        .col-harga-beli { width: 5%; }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 8px;
            font-size: 7px;
            color: #555;
            text-align: right;
        }

        /* ================= NUMBERS ================= */
        .number {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>
        LAPORAN MUTASI BARANG PERSEDIAAN<br>
        SAMPAI DENGAN TANGGAL {{ strtoupper($tanggalAkhir) }}
    </h1>
    <div class="periode-info">
        PERIODE: {{ $periode->year }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" class="col-no">No</th>
            <th rowspan="2" class="col-nama">NAMA BARANG</th>
            <th rowspan="2" class="col-satuan">Satuan</th>

            <th colspan="3" class="bg-gray">SALDO AKHIR {{ $tanggalSaldoAkhir }}</th>
            <th colspan="3" class="bg-gray">PENGADAAN s/d {{ $tanggalAkhir }}</th>
            <th colspan="3" class="bg-gray">JUMLAH SAMPAI s/d {{ $tanggalAkhir }}</th>
            <th colspan="3" class="bg-gray">JUMLAH PENGGUNAAN s/d {{ $tanggalAkhir }}</th>
            <th colspan="4" class="bg-gray">SISA PER {{ $tanggalSisaPer }}</th>
        </tr>
        <tr>
            {{-- Saldo Akhir --}}
            <th class="col-vol bg-gray">Vol</th>
            <th class="col-harga bg-gray">Harga</th>
            <th class="col-total bg-gray">Total</th>
            
            {{-- Pengadaan --}}
            <th class="col-vol bg-gray">Vol</th>
            <th class="col-harga bg-gray">Harga</th>
            <th class="col-total bg-gray">Total</th>
            
            {{-- Jumlah Sampai --}}
            <th class="col-vol bg-gray">Vol</th>
            <th class="col-harga bg-gray">Harga</th>
            <th class="col-total bg-gray">Total</th>
            
            {{-- Jumlah Penggunaan --}}
            <th class="col-vol bg-gray">Vol</th>
            <th class="col-harga bg-gray">Harga</th>
            <th class="col-total bg-gray">Total</th>
            
            {{-- Sisa Per --}}
            <th class="col-vol bg-gray">Vol</th>
            <th class="col-harga bg-gray">Harga</th>
            <th class="col-harga-beli bg-gray">Harga<br>Pembelian<br>Terakhir</th>
            <th class="col-total bg-gray">Total</th>
        </tr>
    </thead>

    <tbody>
        @php
            $no = 1;
        @endphp

        @foreach ($reportData as $category)

            {{-- CATEGORY HEADER --}}
            <tr>
                <td colspan="19" class="category-header">
                    {{ strtoupper($category['name']) }}
                </td>
            </tr>

            {{-- ITEMS --}}
            @foreach ($category['items'] as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-left">{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['satuan'] }}</td>

                    {{-- SALDO AKHIR --}}
                    <td class="text-right number">
                        {{ $item['saldo_akhir']['vol'] > 0 ? number_format($item['saldo_akhir']['vol'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['saldo_akhir']['harga'] > 0 ? number_format($item['saldo_akhir']['harga'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['saldo_akhir']['total'] > 0 ? number_format($item['saldo_akhir']['total'], 0, ',', '.') : '-' }}
                    </td>

                    {{-- PENGADAAN --}}
                    <td class="text-right number">
                        {{ $item['pengadaan']['vol'] > 0 ? number_format($item['pengadaan']['vol'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['pengadaan']['harga'] > 0 ? number_format($item['pengadaan']['harga'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['pengadaan']['total'] > 0 ? number_format($item['pengadaan']['total'], 0, ',', '.') : '-' }}
                    </td>

                    {{-- JUMLAH SAMPAI --}}
                    <td class="text-right number">
                        {{ $item['jumlah_sampai']['vol'] > 0 ? number_format($item['jumlah_sampai']['vol'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['jumlah_sampai']['harga'] > 0 ? number_format($item['jumlah_sampai']['harga'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['jumlah_sampai']['total'] > 0 ? number_format($item['jumlah_sampai']['total'], 0, ',', '.') : '-' }}
                    </td>

                    {{-- JUMLAH PENGGUNAAN --}}
                    <td class="text-right number">
                        {{ $item['jumlah_penggunaan']['vol'] > 0 ? number_format($item['jumlah_penggunaan']['vol'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['jumlah_penggunaan']['harga'] > 0 ? number_format($item['jumlah_penggunaan']['harga'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['jumlah_penggunaan']['total'] > 0 ? number_format($item['jumlah_penggunaan']['total'], 0, ',', '.') : '-' }}
                    </td>

                    {{-- SISA PER --}}
                    <td class="text-right number">
                        {{ $item['sisa_per']['vol'] > 0 ? number_format($item['sisa_per']['vol'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['sisa_per']['harga'] > 0 ? number_format($item['sisa_per']['harga'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['sisa_per']['harga_pembelian_terakhir'] > 0 ? number_format($item['sisa_per']['harga_pembelian_terakhir'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right number">
                        {{ $item['sisa_per']['total'] > 0 ? number_format($item['sisa_per']['total'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach

            {{-- TOTAL PER CATEGORY --}}
            <tr class="total-row">
                <td colspan="3" class="text-center">JUMLAH</td>

                {{-- Total Saldo Akhir --}}
                <td class="text-right number">
                    {{ number_format($category['totals']['saldo_akhir_vol'], 0, ',', '.') }}
                </td>
                <td></td>
                <td class="text-right number">
                    {{ number_format($category['totals']['saldo_akhir_total'], 0, ',', '.') }}
                </td>

                {{-- Total Pengadaan --}}
                <td class="text-right number">
                    {{ number_format($category['totals']['pengadaan_vol'], 0, ',', '.') }}
                </td>
                <td></td>
                <td class="text-right number">
                    {{ number_format($category['totals']['pengadaan_total'], 0, ',', '.') }}
                </td>

                {{-- Total Jumlah Sampai --}}
                <td class="text-right number">
                    {{ number_format($category['totals']['jumlah_sampai_vol'], 0, ',', '.') }}
                </td>
                <td></td>
                <td class="text-right number">
                    {{ number_format($category['totals']['jumlah_sampai_total'], 0, ',', '.') }}
                </td>

                {{-- Total Jumlah Penggunaan --}}
                <td class="text-right number">
                    {{ number_format($category['totals']['jumlah_penggunaan_vol'], 0, ',', '.') }}
                </td>
                <td></td>
                <td class="text-right number">
                    {{ number_format($category['totals']['jumlah_penggunaan_total'], 0, ',', '.') }}
                </td>

                {{-- Total Sisa Per --}}
                <td class="text-right number">
                    {{ number_format($category['totals']['sisa_per_vol'], 0, ',', '.') }}
                </td>
                <td></td>
                <td></td>
                <td class="text-right number">
                    {{ number_format($category['totals']['sisa_per_total'], 0, ',', '.') }}
                </td>
            </tr>

        @endforeach
    </tbody>
</table>

<div class="footer">
    Generated at {{ $generatedAt }} | Periode: {{ $periode->year }}
</div>

</body>
</html>