<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #fff; /* Mengubah latar belakang menjadi putih */
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #555;
            width: 100%;
            text-align: center;
            font-size: 24px;
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
            background-color: #fff;
        }

        th, td {
            padding: 5px;
            border: 1px solid #000000;
            vertical-align: middle; /* Menyelaraskan isi secara vertikal di tengah */
        }

        th {
            background-color: #0068fa;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        tr {
            background-color: #fff;
        }

        /* CSS khusus untuk kolom "No" */
        th.no, td.no {
            text-align: center; /* Menempatkan isi kolom "No" ke tengah */
        }

    </style>
</head>

<body>
    <h2>Laporan Deviasi</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="no">No</th> 
                    <th>Nama Anggaran</th>
                    <th>Rencana Anggaran</th>
                    <th>Realisasi Anggaran</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deviasis as $index => $deviasi)
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ $deviasi->nama_anggaran }}</td>
                        <td>Rp. {{ number_format($deviasi->nominal, 0) }}</td>
                        <td>Rp. {{ number_format($deviasi->nominal_diapprove, 0) }}</td>
                        <td>{{ $deviasi->nominal_diapprove  != 0 ? ($deviasi->nominal_diapprove / $deviasi->nominal) * 100 : '0'  }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
