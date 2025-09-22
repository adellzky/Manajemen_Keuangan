<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $tim->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 30px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        p {
            margin: 2px 0;
        }
        h3 {
            margin-top: 20px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background-color: #3498db;
            color: white;
            text-align: center;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9; /* warna belang */
        }
        tfoot th {
            background-color: #3498db;
            color: white;
            text-align: right;
            font-weight: bold;
        }
        tfoot td {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>Slip Gaji</h2>
    <p><b>Nama:</b> {{ $tim->nama }}</p>
    <p><b>No. Rekening:</b> {{ $tim->norek }} ({{ $tim->atm }})</p>

    <h3>Rincian</h3>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Project</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gajis as $gaji)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($gaji->tanggal)->translatedFormat('d F Y') }}</td>
                    <td>{{ $gaji->project->nama_project ?? '-' }}</td>
                    <td style="text-align:right">Rp {{ number_format($gaji->jumlah,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total Gaji:</th>
                <th style="text-align:right">Rp {{ number_format($tim->gaji,0,',','.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
