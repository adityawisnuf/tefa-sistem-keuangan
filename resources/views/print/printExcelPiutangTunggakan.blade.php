<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATA PIUTANG DAN TUNGGAKAN SISWA</title>
    <style>
        .student-info { margin-bottom: 20px; }
        .student-info p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .title { text-align: center; font-size: 24px; margin-bottom: 20px; }
        .sisa-tagihan { margin-top: 10px; font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>

    @php
        function getNamaBulan($bulan) {
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei',
                6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            return $namaBulan[$bulan] ?? 'Tidak Valid';
        }
    @endphp

    <h2 class="title">DATA PIUTANG DAN TUNGGAKAN SISWA</h2>

    @foreach ($data as $index => $siswa)
    <div class="student-info">
        <p>Nama Siswa: {{ $siswa['nama_siswa'] }}</p>
        <p>Kelas: {{ $siswa['kelas'] }}</p>
        <p>Jurusan: {{ $siswa['jurusan'] }}</p>
        <p>Telepon: {{ $siswa['telepon'] }}</p>
        <p>Orang Tua: {{ $siswa['orang_tua'] }}</p>
        <p class="sisa-tagihan">Sisa Tagihan: Rp. {{ number_format($siswa['sisa_tagihan'] ?? 0, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pembayaran Ke</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Jatuh Tempo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswa['piutang'] as $piutang)
            <tr>
                <td>{{ $piutang['pembayaran_ke'] }}</td>
                <td>Rp. {{ number_format($piutang['nominal'], 0, ',', '.') }}</td>
                <td>{{ $piutang['status'] }}</td>
                <td>{{ $piutang['due_date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Pembayaran Ke</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Jatuh Tempo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswa['tunggakan'] as $tunggakan)
            <tr>
                <td>{{ $tunggakan['pembayaran_ke'] }}</td>
                <td>Rp. {{ number_format($tunggakan['nominal'], 0, ',', '.') }}</td>
                <td>{{ $tunggakan['status'] }}</td>
                <td>{{ $tunggakan['due_date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>
    @endforeach

</body>
</html>