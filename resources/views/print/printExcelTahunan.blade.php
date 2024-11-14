<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATA PEMBAYARAN TAHUNAN SISWA</title>
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

    <h2 class="title">DATA PEMBAYARAN TAHUNAN SISWA</h2>

    @foreach ($data as $index => $siswa)
    <div class="student-info">
        <p><strong>Nama Siswa:</strong> {{ $siswa['nama_siswa'] }}</p>
        <p><strong>Kelas:</strong> {{ $siswa['kelas'] }}</p>
        <p><strong>Jurusan:</strong> {{ $siswa['jurusan'] }}</p>
        <p><strong>Telepon:</strong> {{ $siswa['telepon'] }}</p>
        <p><strong>Orang Tua:</strong> {{ $siswa['orangtua'] ?? 'Data tidak tersedia' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pembayaran</th>
                <th>Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswa['payments'] as $paymentIndex => $payment)
            <tr>
                <td>{{ $paymentIndex + 1 }}</td>
                <td>{{ $payment['pembayaran_ke'] }}</td>
                <td>Rp. {{ number_format($payment['nominal'], 0, ',', '.') }}</td>
                <td>{{ $payment['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="sisa-tagihan"><strong>Sisa Tagihan:</strong> Rp. {{ number_format($siswa['sisa_tagihan'], 0, ',', '.') }}</p>

    @if (!$loop->last)
        <div style="margin-top: 30px;"></div> <!-- Spasi antar data siswa -->
    @endif
    @endforeach

</body>
</html>
