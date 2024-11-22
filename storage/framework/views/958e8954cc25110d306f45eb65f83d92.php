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

    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="student-info">
        <p><strong>Nama Siswa:</strong> <?php echo e($siswa['nama_siswa']); ?></p>
        <p><strong>Kelas:</strong> <?php echo e($siswa['kelas']); ?></p>
        <p><strong>Jurusan:</strong> <?php echo e($siswa['jurusan']); ?></p>
        <p><strong>Telepon:</strong> <?php echo e($siswa['telepon']); ?></p>
        <p><strong>Orang Tua:</strong> <?php echo e($siswa['orangtua'] ?? 'Data tidak tersedia'); ?></p>
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
            <?php $__currentLoopData = $siswa['payments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paymentIndex => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($paymentIndex + 1); ?></td>
                <td><?php echo e($payment['pembayaran_ke']); ?></td>
                <td>Rp. <?php echo e(number_format($payment['nominal'], 0, ',', '.')); ?></td>
                <td><?php echo e($payment['status']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <p class="sisa-tagihan"><strong>Sisa Tagihan:</strong> Rp. <?php echo e(number_format($siswa['sisa_tagihan'], 0, ',', '.')); ?></p>

    <?php if(!$loop->last): ?>
        <div style="margin-top: 30px;"></div> <!-- Spasi antar data siswa -->
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</body>
</html>
<?php /**PATH C:\laragon-php-8-mariadb-11\laragon-6.0-portable\www\kelompok-1-fixing\tefa-sistem-keuangan\resources\views/print/printExcelTahunan.blade.php ENDPATH**/ ?>