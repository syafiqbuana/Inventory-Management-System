{{-- resources/views/filament/resources/item-resource/report/item-history-report.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Barang - {{ $item->name }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* PAGE */
        .page {
            page-break-after: always;
        }

        /* TITLE */
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        /* ITEM INFO */
        .item-info {
            margin-bottom: 10px;
        }

        .item-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .item-info td {
            padding: 4px;
        }

        /* TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        .data-table th {
            border: 1px solid black;
            padding: 5px;
            background: #efefef;
            text-align: center;
        }

        .data-table td {
            border: 1px solid #444;
            padding: 4px;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        /* SIGNATURE */
        .signature {
            margin-top: 60px;
            width: 100%;
        }

        .signature-box {
            width: 260px;
            margin-left: auto;
            text-align: center;
            font-size: 11px;
        }

        .signature-name {
            margin-top: 70px;
            font-weight: bold;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 5px;
            padding-top: 3px;
        }

        .signature-nip {
            font-size: 10px;
        }

        /* EMPTY MESSAGE */
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
            font-size: 11px;
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

    <div class="page">

        <div class="title">
            KARTU BARANG
        </div>

        {{-- INFO BARANG --}}
        <div class="item-info">
            <table>
                <tr>
                    <td width="20%">Nama Barang</td>
                    <td width="2%">:</td>
                    <td>{{ $item->name }}</td>
                </tr>

                <tr>
                    <td>Kategori</td>
                    <td>:</td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Satuan</td>
                    <td>:</td>
                    <td>{{ $satuan }}</td>
                </tr>

                <tr>
                    <td>Stok Akhir</td>
                    <td>:</td>
                    <td>{{ number_format($finalStock, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- TABEL TRANSAKSI --}}
        @if ($transactions->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Tanggal</th>
                        <th width="15%">SBBM/SBBK</th>
                        <th width="10%">Masuk</th>
                        <th width="10%">Keluar</th>
                        <th width="10%">Sisa</th>
                        <th width="18%">Harga</th>
                        <th width="20%">Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($transactions as $i => $trans)
                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td>
                                @if ($trans['date'])
                                    {{ $trans['date'] }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-left">
                                {{ $trans['reference'] ?? '-' }}
                            </td>

                            <td>
                                @if ($trans['qty_in'] > 0)
                                    {{ number_format($trans['qty_in'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($trans['qty_out'] > 0)
                                    {{ number_format($trans['qty_out'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right">
                                <strong>{{ number_format($trans['sisa'], 0, ',', '.') }}</strong>
                            </td>

                            <td class="text-right">
                                @if ($trans['unit_price'] > 0)
                                    {{ fmtNum($trans['unit_price']) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-left">
                                {{ $trans['notes'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                Belum ada transaksi untuk barang ini
            </div>
        @endif

        {{-- SIGNATURE --}}
        <div class="signature">
            <div class="signature-box">
                <div>
                    Purwokerto, {{ now()->translatedFormat('d F Y') }}
                </div>

                <div>
                    Pengurus Barang
                </div>

                <div class="signature-name">
                    <div style="height:60px;"></div>
                </div>

                <div class="signature-line">
                    (TRISETIAWAN)
                </div>

                <div class="signature-nip">
                    NIP : 19851107 201406 1 003
                </div>
            </div>
        </div>

    </div>
</body>

</html>