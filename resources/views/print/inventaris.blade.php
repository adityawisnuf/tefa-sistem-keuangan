<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px;
            border: 1px solid #000;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body style="display: flex; flex-direction: column; align-items: center; text-align: center">
    <h2>Laporan Inventaris</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal Ditambahkan</th>
                <th>Aset</th>
                <th>Kondisi</th>
                <th>Penggunaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assets as $asset)
                <tr>
                    <td>{{ $asset->created_at->format('d-m-Y') }}</td>
                    <td>{{ $asset->nama }}</td>
                    <td>{{ $asset->kondisi }}</td>
                    <td>{{ $asset->penggunaan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
