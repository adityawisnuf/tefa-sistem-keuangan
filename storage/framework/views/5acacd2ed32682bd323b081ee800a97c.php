<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATA PEMBAYARAN SPP SISWA</title>
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

    <?php
        function getNamaBulan($bulan) {
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei',
                6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            return $namaBulan[$bulan] ?? 'Tidak Valid';
        }
    ?>

    <h2 class="title">DATA PEMBAYARAN SPP SISWA</h2>

    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="student-info">
        <p>Nama Siswa: <?php echo e($siswa['nama_siswa']); ?></p>
        <p>Kelas: <?php echo e($siswa['kelas']); ?></p>
        <p>Jurusan: <?php echo e($siswa['jurusan']); ?></p>
        <p>Telepon: <?php echo e($siswa['telepon']); ?></p>
        <p>Orang Tua: <?php echo e($siswa['orangtua']); ?></p>
        <p class="sisa-tagihan">Sisa Tagihan: Rp. <?php echo e(number_format($siswa['sisa_tagihan'] ?? 0, 0, ',', '.')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pembayaran Ke</th>
                <th>Bulan</th>
                <th>Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $siswa['payments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($payment['pembayaran_ke']); ?></td>
                <td><?php echo e(getNamaBulan($payment['bulan'])); ?></td>
                <td>Rp. <?php echo e(number_format($payment['nominal'], 0, ',', '.')); ?></td>
                <td><?php echo e($payment['status']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <br><br>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</body>
</html>
<?php /**PATH C:\laragon-php-8-mariadb-11\laragon-6.0-portable\www\kelompok-1-fixing\tefa-sistem-keuangan\resources\views/print/PrintExcelSPP.blade.php ENDPATH**/ ?>