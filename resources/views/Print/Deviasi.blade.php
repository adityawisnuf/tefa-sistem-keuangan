<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Deviasi</title>
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
            background-color: #fff; /* Mengubah latar belakang menjadi putih */
            color: #151010;
            margin: 0;
            padding: 20px;
        }

          /* Header styling */
          header {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 1000px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            position: relative;
        }

        .logo {
            position: absolute;
            width: 90px;
            height: auto;
        }

        .kop-surat {
            text-align: center;
            flex: 1;
        }

        .kop-surat h1, .kop-surat p, .kop-surat h2 {
            margin: 0;
        }
 .kop-surat h2 {
            font-size: 16px;
            font-weight: normal; /* Mengatur font-weight menjadi normal */
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
            margin-top: 5px; /* Mengurangi jarak antara garis dan kop surat */
        }

 .line-container hr {
            border: 1px solid black; /* Mengubah ketebalan garis menjadi lebih tipis */
            margin: 2px 0; /* Mengurangi jarak antar dua garis */
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

        th, td {
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
            text-align: right; /* Mengarahkan elemen ke kanan */
            padding-right: 20px; /* Memberi jarak dari tepi kanan */
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
            <h1>SMK NEGERI 2 SUMEDANG</h1>
            <p>Jalan Arief Rakhman Hakim No. 59 Telp. 0216-201531, Fax. 0261-210097</p>
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

    <h3>LAPORAN DEVIASI</h3>
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
    
</body>

</html>