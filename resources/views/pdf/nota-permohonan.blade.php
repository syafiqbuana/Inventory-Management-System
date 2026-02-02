<!DOCTYPE html>
<html>

<head>
    <title>Nota Permohonan Barang</title>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
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

        .info {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }

        .info table {
            width: 100%;
            border: none;
        }

        .info td {
            padding: 4px 0;
            font-size: 12px;
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }

        th {
            background-color: #e0e0e0;
            text-align: center;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .signature {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature td {
            border: none;
            text-align: center;
            font-size: 12px;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Nota Permohonan Barang</h1>
        <p>Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
    </div>

    {{-- INFO PEMOHON --}}
    <div class="info">
        <table>
            <tr>
                <td width="20%"><strong>Nama Penerima</strong></td>
                <td width="2%">:</td>
                <td>{{ $usage->used_by }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Permohonan</strong></td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($usage->usage_date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Keperluan</strong></td>
                <td>:</td>
                <td>{{ $usage->used_for }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL ITEM --}}
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Barang</th>
                <th width="25%">Kategori</th>
                <th width="15%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usage->usageItems as $index => $detail)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $detail->item->name ?? '-' }}</td>
                    <td class="center">{{ $detail->item->category->name ?? '-' }}</td>
                    <td class="center">
                        {{ $detail->qty }} ({{ $detail->item->itemType->name?? '-' }})
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="signature">
        <tr>
            <td width="50%">
                Yang Menerima<br><br><br><br><br><br>
                <strong>{{ $usage->used_by }}</strong>
            </td>
            <td width="50%">
                Yang Menyerahkan<br><br><br><br><br><br>
                <strong>TRI SETIAWAN</strong>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem</p>
    </div>

</body>

</html>
