<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
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

        /* Footer */
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

    <h2>Laporan Pendapatan</h2>

    <table>
        <thead>
            <tr>
                <th>Nama Project</th>
                <th>Mitra</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data->id_pendapatan)) 
                <tr>
                    <td>{{ $data->project->nama_project ?? '-' }}</td>
                    <td>{{ $data->mitra->instansi ?? '-' }}</td>
                    <td>Rp {{ number_format($data->jumlah, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $data->keterangan }}</td>
                </tr>
            @else
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item->project->nama_project ?? '-' }}</td>
                        <td>{{ $item->mitra->instansi ?? '-' }}</td>
                        <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $item->keterangan }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>
