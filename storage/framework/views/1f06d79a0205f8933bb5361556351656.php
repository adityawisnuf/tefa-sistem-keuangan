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
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1000px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000; /* Garis di bawah kop surat */
        }

        .logo {
            width: 100px;
            height: auto; /* Menjaga rasio gambar */
        }

        .kop-surat {
            text-align: center;
            flex: 1;
        }

        .kop-surat h1 {
            font-size: 24px;
            margin: 0;
        }

        .kop-surat p {
            margin: 0;
            font-size: 14px;
        }

        /* Judul Laporan */
        h3 {
            margin-bottom: 20px;
            color: #080000;
            width: 100%;
            text-align: center;
            font-size: 24px;
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
            width: 100%;
            max-width: 1000px;
            text-align: right;
            margin-top: 50px;
        }

        .signature {
            margin-top: 80px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- Kop Surat -->
    <header>
        <!-- Logo Sekolah -->
        <img src="<?php echo e(asset('foto/logosmk.jpeg')); ?>" alt="Logo Sekolah" class="logo">
        <div class="kop-surat">
            <h1>SMKN 2 SUMEDANG</h1>
            <p>Jalan Arief Rakhman Hakim No. 59 45355 Sumedang, West Java, Jawa Barat</p>
            <p>Telepon: (0261) 201531 | Email: info@sekolah.com</p>
        </div>
    </header>

    <!-- Judul Laporan -->
    <h3>Laporan Inventaris</h3>

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
                <?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($asset->nama); ?></td>
                    <td><?php echo e($asset->kondisi); ?></td>
                    <td><?php echo e($asset->penggunaan); ?></td>
                    <td><?php echo e($asset->tipe); ?></td>
                    <td><?php echo e('Rp ' . number_format($asset->harga, 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Area Tanda Tangan -->
    <div class="signature-section">
        <p>Sumedang <?php echo e(date('d F Y')); ?></p>
        <div class="signature">
            <p>Kepala Sekolah</p>
            <p>_______________________</p> <!-- Tanda tangan di sini -->
        </div>
    </div>
</body>

</html>|<?php /**PATH D:\LARAGON\laragon-6.0-portable\www\Kelompok 4\tefa-sistem-keuangan\resources\views\print\inventaris.blade.php ENDPATH**/ ?>