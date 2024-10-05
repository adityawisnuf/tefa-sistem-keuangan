<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inventaris</title>
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
        header {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 1000px;
            margin-bottom: 10px;
        }

        .logo {
            position: absolute;
            width: 90px; 
            margin-right: 20px;
        }

        .kop-surat {
            text-align: center;
            flex: 1;
        }

        .kop-surat h1,
        .kop-surat p,
        .kop-surat h2 {
            margin: 0;
        }

        .kop-surat h2 {
            font-size: 16px;
            font-weight: normal;
        }

        .kop-surat h1 {
            font-size: 18px;
            font-weight: bold;
        }

        .kop-surat p {
            font-size: 10px;
        }

        .kop-surat .kabupaten {
            font-size: 11px;
        }

        /* Garis bawah kop surat */
        .line-container {
            width: 100%;
            max-width: 1000px;
            margin-top: 5px;
        }

        .line-container hr {
            border: 1px solid black;
            margin: 2px 0;
        }

        /* Judul Laporan */
        h3 {
            margin-bottom: 20px;
            color: #080000;
            width: 100%;
            text-align: center;
            font-size: 20px;
            text-decoration: underline;
        }

        /* Table styling */
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
            font-size: 14px;
        }

        th,
        td {
            padding: 5px;
            border: 1px solid #000000;
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

        /* Tanda tangan styling */
        .signature-section {
            width: fit-content;
            max-width: 1000px;
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
    <!-- Kop Surat -->
    <header>
        <!-- Logo Sekolah -->
        <img src="{{ public_path($sekolah->logo) }}" class="logo" />
        <div class="kop-surat">
            <h2>PEMERINTAH DAERAH PROVINSI JAWA BARAT</h2>
            <h2>DINAS PENDIDIKAN</h2>
            <h2>CABANG DINAS PENDIDIKAN WILAYAH VIII</h2>
            <h1>{{ $sekolah->nama }}</h1>
            <p>{{ $sekolah->alamat }} Telp. 0216-201531, Fax. 0261-210097</p>
            <p>http://www.smkn2sumedang.sch.id - email.smkn2sumedang@yahoo.com</p>
            <p class="kabupaten">KABUPATEN SUMEDANG 45323</p>
        </div>
    </header>

    <!-- Garis bawah kop surat -->
    <div class="line-container">
        <hr>
        <hr>
    </div>

    <!-- Judul Laporan -->
    <h3>LAPORAN INVENTARIS</h3>

    <!-- Tabel Inventaris -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Kondisi</th>
                    <th>Penggunaan</th>
                    <th>Tipe</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assets as $asset)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $asset->nama }}</td>
                    <td>{{ $asset->getKondisiText() }}</td>
                    <td>{{ $asset->penggunaan }}</td>
                    <td >{{ $asset->getTipeText() }}</td>
                    <td>{{ 'Rp ' . number_format($asset->harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Area Tanda Tangan -->
    <div style="display: flex; justify-content: end;">
        <div style="width: 35%; position: absolute; right: 0;">
            <div class="signature">
                <p>Sumedang, {{ date('d F Y') }}</p>
                <p>Kepala Sekolah,</p>
            </div>
            <div class="signature">
                <p style="font-weight: bold;">Dra. Elis Herawati, M.Pd.</p>
                <p>{{ $sekolah->nip_kepsek }}</p>
            </div>
        </div>
    </div>
</body>

</html>
