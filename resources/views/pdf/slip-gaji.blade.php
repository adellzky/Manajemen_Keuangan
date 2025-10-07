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
            background-color: #fff;
        }

        .kop-surat {
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 8px;
            margin-bottom: 25px;
        }

        .kop-surat table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-surat td {
            border: none;
        }

        .kop-surat img {
            max-height: 65px;
        }

        .kop-surat h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #222;
        }

        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
            color: #444;
        }

        h2 {
            text-align: center;
            margin: 15px 0;
            font-weight: 600;
            color: #222;
        }

        h3 {
            text-align: center;
            margin-bottom: 12px;
            font-size: 13px;
            color: #007BFF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #dcdcdc;
            padding: 6px 8px;
        }

        th {
            background-color: #007BFF;
            color: white;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fbff;
        }

        tbody tr:hover {
            background-color: #eef6ff;
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

        .total-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .total-section th {
            background-color: #007BFF;
            color: white;
            text-align: left;
            padding: 8px;
            font-size: 13px;
            border-radius: 6px 0 0 6px;
        }

        .total-section td {
            background-color: #007BFF;
            color: white;
            text-align: right;
            padding: 8px;
            font-weight: bold;
            border-radius: 0 6px 6px 0;
        }

        .footer {
            margin-top: 25px;
            font-size: 11px;
            color: #555;
            text-align: left;
        }
    </style>
</head>

<body>

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

    <h2>Slip Gaji</h2>
    <p><b>Periode:</b> {{ \Carbon\Carbon::create()->month($bulan)->year($tahun)->translatedFormat('F Y') }}</p>
    <p><b>Nama:</b> {{ $tim->nama }}</p>
    <p><b>No. Rekening:</b> {{ $tim->norek }} ({{ $tim->atm }})</p>

    <h3>Rincian Gaji Bulan {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}</h3>

    <table style="width:100%; border:none;">
        <tr>
            <td style="vertical-align:top; width:50%; padding-right:10px;">
                <h4 style="text-align:center;">Pendapatan</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Project</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gajis as $gaji)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($gaji->tanggal)->translatedFormat('d M Y') }}</td>
                                <td>{{ $gaji->project->nama_project ?? '-' }}</td>
                                <td style="text-align:right;">Rp {{ number_format($gaji->jumlah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total Pendapatan</th>
                            <th style="text-align:right;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <td style="vertical-align:top; width:50%; padding-left:10px;">
                <h4 style="text-align:center;">Potongan</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($potongans as $p)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_bayar)->translatedFormat('d M Y') }}</td>
                                <td>{{ $p->keterangan ?? 'Cicilan Hutang' }}</td>
                                <td style="text-align:right;">Rp {{ number_format($p->nominal_cicilan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center;">Tidak ada potongan</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total Potongan</th>
                            <th style="text-align:right;">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding-top:12px;">
                <table class="total-section">
                    <tr>
                        <th>Gaji Bersih</th>
                        <td>Rp {{ number_format($gajiBersih, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>
