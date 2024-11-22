<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Buku Kas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h3 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .export-button {
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .export-button:hover {
            background-color: #45a049;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: center; 
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <h3>Laporan Buku Kas</h3>
    <a href="{{ route('pengeluaran.exportExcel') }}">Export Pengeluaran</a>
    <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengeluaran</th>
                <th>Keperluan</th>
                <th>Nominal</th>
                <th>Tanggal Diajukan</th>
                <th>Tanggal Disetujui</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($pengeluaran as $pengeluaran)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pengeluaran->pengeluaran_kategori ? $pengeluaran->pengeluaran_kategori->nama : 'Data tidak tersedia' }}</td>
                        <td>{{ $pengeluaran->keperluan }}</td>
                        <td>{{ 'Rp ' . number_format($pengeluaran->nominal, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($pengeluaran->diajukan_pada)->format('d-m-Y') }}</td>
                        <td>{{ $pengeluaran->disetujui_pada ? \Carbon\Carbon::parse($pengeluaran->disetujui_pada)->format('d-m-Y') : 'Belum disetujui' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Data tidak ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
