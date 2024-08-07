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
    <h2>Laporan Inventaris</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Kondisi</th>
                    <th>Tipe</th>
                    <th>Penggunaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assets as $asset)
                    <tr>
                        <td>{{ $asset->id }}</td>
                        <td>{{ $asset->nama }}</td>
                        <td>{{ $asset->harga }}</td>
                        <td>{{ $asset->kondisi }}</td>
                        <td>{{ $asset->tipe }}</td>
                        <td>{{ $asset->penggunaan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
