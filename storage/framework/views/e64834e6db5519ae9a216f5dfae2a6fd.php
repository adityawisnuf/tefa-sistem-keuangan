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

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <!-- Kop Surat -->
    <header>
        <img src="<?php echo e(public_path($sekolah->logo)); ?>" class="logo" />
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

    <div class="line-container">
        <hr>
        <hr>
    </div>

    <h3>Laporan Piutang dan Tunggakan Siswa</h3>

    <!-- Tabel Piutang dan Tunggakan -->
    <div class="table-container">
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
                <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($key + 1); ?></td>
                        <td><?php echo e($siswa['nama_siswa']); ?></td>
                        <td><?php echo e($siswa['kelas']); ?></td>
                        <td><?php echo e($siswa['jurusan']); ?></td>
                        <td><?php echo e($siswa['telepon']); ?></td>
                        <td><?php echo e($siswa['orang_tua']); ?></td>
    
                        <!-- Piutang Section (Updated) -->
                        <td>
                            <?php $__currentLoopData = $siswa['piutang']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $piutang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="student-data <?php echo e($index == 0 ? 'first-payment' : ''); ?>">
                                    <div class="student-info">
                                        <p><strong>Nama Siswa:</strong> <?php echo e($siswa['nama_siswa']); ?></p>
                                        <p><strong>Kelas:</strong> <?php echo e($siswa['kelas']); ?></p>
                                        <p><strong>Jurusan:</strong> <?php echo e($siswa['jurusan']); ?></p>
                                        <p><strong>Telepon:</strong> <?php echo e($siswa['telepon']); ?></p>
                                        <p><strong>Orang Tua:</strong> <?php echo e($siswa['orang_tua']); ?></p>
                                        <p><strong>Sisa Piutang:</strong> Rp<?php echo e(number_format($piutang['sisa_piutang'], 0, ',', '.')); ?></p>
                                    </div>
    
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Pembayaran Ke</th>
                                                <th>Nominal</th>
                                                <th>Status Pembayaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo e($loop->iteration); ?></td>
                                                <td><?php echo e($piutang['pembayaran_ke']); ?></td>
                                                <td>Rp. <?php echo e(number_format($piutang['nominal'], 0, ',', '.')); ?></td>
                                                <td><?php echo e($piutang['status']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <hr>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
    
                        <!-- Tunggakan Section (Unchanged) -->
                        <td>
                            <?php $__currentLoopData = $siswa['tunggakan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tunggakan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <strong>Pembayaran ke-<?php echo e($tunggakan['pembayaran_ke']); ?></strong><br>
                                    Nominal: Rp<?php echo e(number_format((float)$tunggakan['nominal'], 0, ',', '.')); ?><br>
                                    Jatuh Tempo: <?php echo e($tunggakan['due_date']); ?>

                                </div>
                                <hr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
    
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    

    <!-- Tanda Tangan -->
    <div style="display: flex; justify-content: end;">
        <div style="width: 35%; position: absolute; right: 0;">
            <div class="signature">
                <p>Sumedang, <?php echo e(date('d F Y')); ?></p>
                <p>Kepala Sekolah,</p>
            </div>
            <div class="signature">
                <p style="font-weight: bold;">Dra. Elis Herawati, M.Pd.</p>
                <p><?php echo e($sekolah->nip_kepsek); ?></p>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH C:\laragon-php-8-mariadb-11\laragon-6.0-portable\www\kelompok-1-fixing\tefa-sistem-keuangan\resources\views/print/piutang_tunggakan.blade.php ENDPATH**/ ?>