<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATA PEMBAYARAN SPP SISWA</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            color: #151010;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: underline;
            color: #080000;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
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

        td {
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f4f4f9;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        hr {
            border: 1px solid black;
            margin: 20px 0;
        }

        /* Kop Surat */
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

        /* Styling untuk informasi siswa */
        .student-info p {
            font-size: 12px;
        }

        /* Mengatur pembatas halaman untuk setiap data pembayaran siswa */
        .student-data {
            page-break-before: always;
        }

        .first-payment {
            page-break-before: auto; /* Membuat halaman pertama tidak terpisah */
        }

        /* Pembatas halaman untuk setiap data pembayaran berikutnya */
        .student-data:not(.first-payment) {
            page-break-before: always;
        }

        @media print {
            /* Halaman baru pada setiap data pembayaran siswa, kecuali pembayaran pertama */
            .student-data:not(.first-payment) {
                page-break-before: always;
            }

            /* Menghapus margin agar bisa lebih rapat */
            body {
                margin: 0;
                padding: 0;
            }

            .kop-surat hr {
                display: none; /* Menyembunyikan garis kedua di bawah kop surat */
            }
        }

        /* Garis setelah kop surat */
        .line-container hr {
            border: 1px solid black;
            margin: 20px 0;
        }

        /* Styling untuk area tanda tangan */
        .signature {
            text-align: center;
            margin-top: 50px;
        }

        .signature p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <!-- Kop Surat -->
    <header>
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

    <!-- Garis bawah kop surat (hanya satu garis) -->
    <div class="line-container">
        <hr>
    </div>

    <!-- Judul Laporan Data Siswa dan Pembayaran -->
    <h2 class="title">DATA PEMBAYARAN SPP SISWA</h2>

    @foreach ($result as $index => $data)
        <!-- Untuk pembayaran pertama, gabungkan dengan data siswa pada halaman pertama -->
        <div class="student-data {{ $index == 0 ? 'first-payment' : '' }}">
            <div class="student-info">
                <p><strong>Nama Siswa:</strong> {{ $data['nama_siswa'] }}</p>
                <p><strong>Kelas:</strong> {{ $data['kelas'] }}</p>
                <p><strong>Jurusan:</strong> {{ $data['jurusan'] }}</p>
                <p><strong>Telepon:</strong> {{ $data['telepon'] }}</p>
                <p><strong>Orang Tua:</strong> {{ $data['orangtua'] }}</p>
                <p><strong>Sisa Tagihan:</strong> Rp{{ number_format($data['sisa_tagihan'], 0, ',', '.') }}</p>
            </div>

            <!-- Tabel Pembayaran -->
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Pembayaran Ke</th>
                        <th>Bulan Pembayaran</th>
                        <th>Nominal</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['payments'] as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment['pembayaran_ke'] }}</td>
                            <td>{{ $payment['bulan'] }}</td>
                            <td>Rp. {{ number_format($payment['nominal'], 0, ',', '.') }}</td>
                            <td>{{ $payment['status'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
        </div>
    @endforeach

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
