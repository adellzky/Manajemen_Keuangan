<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Ironative</title>
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

        .kop-surat table { width: 100%; border-collapse: collapse; }
        .kop-surat td { border: none; }
        .kop-surat img { max-height: 70px; }

        .kop-surat h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .kop-surat p { margin: 2px 0; font-size: 11px; }

        h2 { text-align: center; margin: 15px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tbody tr:nth-child(even) { background-color: #f2f9ff; }
        tbody tr:nth-child(odd) { background-color: #ffffff; }

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

    <h2>Laporan Keuangan Ironative</h2>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Setor</th>
                <th>Tarik Cash</th>
                <th>Saldo Cash</th>
                <th>Saldo Bank</th>
                <th>Pendapatan</th>
                <th>Pengeluaran</th>
                <th>Total Gaji</th>
                <th>Total Hutang</th>          {{-- ✅ baru --}}
                <th>Total Cicilan Hutang</th>   {{-- ✅ baru --}}
                <th>Keseluruhan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>Rp {{ number_format($item->modal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->cash_tarik, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->cash, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->saldo_akhir, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_gaji, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_hutang ?? 0, 0, ',', '.') }}</td>         {{-- ✅ baru --}}
                    <td>Rp {{ number_format($item->total_cicilan ?? 0, 0, ',', '.') }}</td>        {{-- ✅ baru --}}
                    <td>Rp {{ number_format($item->keseluruhan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>
