<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Piutang dan Tunggakan Siswa</title>
    <style>
        /* Styling umum */
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

        /* Header styling */
        h1 {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        /* Table styling */
        table {
            width: 100%;
            max-width: 1000px;
            border-collapse: collapse;
            background-color: #ffffff;
            color: #151010;
            font-size: 12px;
        }

        th, td {
            padding: 5px;
            border: 1px solid #000000;
            text-align: center;
        }

        th {
            background-color: #0068fa;
            color: #ffffff;
        }

        tr:nth-child(even) {
            background-color: #f4f4f9;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        .signature-section {
            width: 35%;
            margin-top: 50px;
            text-align: right;
            padding-right: 20px;
        }

        .signature p {
            margin: 5px 0;
        }

        .signature {
            margin-top: 80px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <h1>Laporan Piutang dan Tunggakan Siswa</h1>

    <!-- Tabel Piutang dan Tunggakan -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Telepon</th>
                <th>Orang Tua</th>
                <th>Piutang</th>
                <th>Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataSiswa as $key => $siswa)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $siswa['nama_siswa'] }}</td>
                    <td>{{ $siswa['kelas'] }}</td>
                    <td>{{ $siswa['jurusan'] }}</td>
                    <td>{{ $siswa['telepon'] }}</td>
                    <td>{{ $siswa['orang_tua'] }}</td>
                    <td>
                        @foreach($siswa['piutang'] as $piutang)
                            <div>
                                <strong>Pembayaran ke-{{ $piutang['pembayaran_ke'] }}</strong><br>
                                Nominal: Rp{{ number_format((float)$piutang['nominal'], 0, ',', '.') }}<br>
                                Jatuh Tempo: {{ $piutang['due_date'] }}
                            </div>
                            <hr>
                        @endforeach
                    </td>
                    <td>
                        @foreach($siswa['tunggakan'] as $tunggakan)
                            <div>
                                <strong>Pembayaran ke-{{ $tunggakan['pembayaran_ke'] }}</strong><br>
                                Nominal: Rp{{ number_format((float)$tunggakan['nominal'], 0, ',', '.') }}<br>
                                Jatuh Tempo: {{ $tunggakan['due_date'] }}
                            </div>
                            <hr>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <p>Sumedang, {{ date('d F Y') }}</p>
        <p>Kepala Sekolah,</p>
        <div class="signature">
            <p style="font-weight: bold;">Dra. Elis Herawati, M.Pd.</p>
            <p>{{ $sekolah->nip_kepsek }}</p>
        </div>
    </div>

</body>
</html>
