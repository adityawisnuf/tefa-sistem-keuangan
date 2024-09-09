<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: 'Arial', sans-serif;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #ffffff;
            color: #151010;
            margin: 0;
            padding: 20px;
        }

        h3 {
            margin-bottom: 20px;
            color: #080000;
            width: 100%;
            text-align: center;
            font-size: 24px; /* Ukuran font lebih besar untuk judul */
        }

        .table-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            max-width: 1000px;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: #151010;
            font-size: 14px; /* Ukuran font tetap untuk tabel */
        }

        th, td {
            padding: 5px;
            border: 1px solid #000000; /* Border hitam */
        }

        th {
            background-color: #0068fa;
            color: #ffffff;
        }

        tr:nth-child(even) {
            background-color: #f4f4f9; /* Warna abu-abu muda untuk baris genap */
        }

        tr:hover {
            background-color: #e0e0e0; /* Warna abu-abu terang saat hover */
        }

        th, td {
            text-align: left;
        }
    </style>
</head>

<body>
    <h3>Laporan Buku Kas</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Transaksi</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Pembayaran</th>
                    <th>Nominal</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pembayarans as $pembayaran)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pembayaran->kode_transaksi }}</td>
                        <td>{{ $pembayaran->nama_siswa }}</td>
                        <td>{{ $pembayaran->kelas }}</td>
                        <td>{{ $pembayaran->jenis_pembayaran }}</td>
                        <td>{{ 'Rp ' . number_format($pembayaran->nominal, 0, ',', '.') }}</td>
                        <td>{{ $pembayaran->created_at->format('d-m-Y') }}</td>
                        <td>{{ $pembayaran->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
