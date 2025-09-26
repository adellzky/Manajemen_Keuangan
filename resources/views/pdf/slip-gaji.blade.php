<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $tim->nama }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .kop-surat {
            width: 100%;
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .kop-surat table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-surat td {
            border: none;
        }

        .kop-surat img {
            max-height: 70px;
        }

        .kop-surat h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f9ff;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tfoot th {
            background-color: #007BFF;
            color: #fff;
            text-align: right;
        }

        tfoot td {
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: left;
            font-size: 11px;
        }
    </style>
</head>
<body>

    {{-- HEADER / KOP SURAT --}}
    <div class="kop-surat">
        <table>
            <tr>
                <td style="width: 80px; text-align: left;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo">
                </td>
                <td style="text-align: center;">
                    <h1>CV. ALZEN METRO DATA</h1>
                    <p><em>Multimedia Broadcasting, Software Development, and IT Solution</em></p>
                    <p><strong>Perum Pesat Gatra Village Blok O No. 1, Kel. Bakungan,</strong></p>
                    <p><strong>Kec. Glagah, Kab. Banyuwangi - Jawa Timur</strong></p>
                    <p>Telp: +62 81 559 555 555 | www.AlzenMetroData.com</p>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
    </div>

    <h2>Bukti Pembayaran Gaji</h2>
    <p><b>Nama:</b> {{ $tim->nama }}</p>
    <p><b>No. Rekening:</b> {{ $tim->norek }} ({{ $tim->atm }})</p>

    <h3>Rincian Gaji Per Project</h3>
<table>
    <thead>
        <tr>
            <th>Bulan</th>
            <th>Project</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($gajis->whereNotNull('id_project') as $gaji)
            <tr>
                <td>{{ \Carbon\Carbon::parse($gaji->tanggal)->translatedFormat('d F Y') }}</td>
                <td>{{ $gaji->project->nama_project ?? '-' }}</td>
                <td style="text-align:right">Rp {{ number_format($gaji->jumlah,0,',','.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h3>Rincian Gaji</h3>
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Project / Keterangan</th>
            <th>Nominal</th>
        </tr>
    </thead>
    <tbody>
        {{-- Gaji per project --}}
        @foreach($gajis->whereNotNull('id_project') as $gaji)
            <tr>
                <td>{{ \Carbon\Carbon::parse($gaji->tanggal)->translatedFormat('d F Y') }}</td>
                <td>{{ $gaji->project->nama_project ?? '-' }} <span style="color: green;">(Gaji Project)</span></td>
                <td style="text-align:right">Rp {{ number_format($gaji->jumlah,0,',','.') }}</td>
            </tr>
        @endforeach

        {{-- Ambil gaji tanpa project --}}
        @foreach($gajis->whereNull('id_project') as $gaji)
            <tr>
                <td>{{ \Carbon\Carbon::parse($gaji->tanggal)->translatedFormat('d F Y') }}</td>
                <td><span style="color: rgb(255, 0, 0);">Pengambilan Gaji</span></td>
                <td style="text-align:right">Rp {{ number_format($gaji->jumlah,0,',','.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">Total Gaji Tersisa</th>
            <th style="text-align:right">Rp {{ number_format($tim->gaji,0,',','.') }}</th>
        </tr>
    </tfoot>
</table>



    <div class="footer" style="text-align: left;">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>
