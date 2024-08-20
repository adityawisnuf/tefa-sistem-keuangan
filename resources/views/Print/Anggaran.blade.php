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
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #555;
            width: 100%;
            text-align: center;
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
            border: 1px solid #ddd;
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
            background-color: #fff; /* Ensure rows have a solid background */
        }

        th, td {
            text-align: center;
        }
    </style>
</head>

<body>
    <h2>Laporan Anggaran</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Anggaran</th>
                    <th>Nominal</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Target Terealisasikan</th>
                    <th>Status</th>
                    <th>Pengapprove</th>
                    <th>Pengapprove Jabatan</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anggarans as $anggaran)
                    <tr>
                        <td>{{ $anggaran->id }}</td>
                        <td>{{ $anggaran->nama_anggaran }}</td>
                        <td>{{ $anggaran->nominal }}</td>
                        <td>{{ $anggaran->deskripsi }}</td>
                        <td>{{ $anggaran->tanggal_pengajuan }}</td>
                        <td>{{ $anggaran->target_terealisasikan }}</td>
                        <td>{{ $anggaran->status }}</td>
                        <td>{{ $anggaran->pengapprove }}</td>
                        <td>{{ $anggaran->pengapprove_jabatan }}</td>
                        <td>{{ $anggaran  ->catatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
