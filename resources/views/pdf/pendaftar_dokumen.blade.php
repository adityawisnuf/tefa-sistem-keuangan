<!DOCTYPE html>
<html>
<head>
    <title>Pendaftar Dokumen</title>
    <style>
        /* Styling PDF */
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Data Pendaftar Dokumen</h1>
    <p><strong>ID:</strong> {{ $pendaftarDokumen->id }}</p>
    <p><strong>Akte Kelahiran:</strong></p>
    <img src="{{ asset('storage/' . $pendaftarDokumen->akte_kelahiran) }}" alt="Akte Kelahiran">
    <p><strong>Kartu Keluarga:</strong></p>
    <img src="{{ asset('storage/' . $pendaftarDokumen->kartu_keluarga) }}" alt="Kartu Keluarga">
    <p><strong>Ijazah:</strong></p>
    <img src="{{ asset('storage/' . $pendaftarDokumen->ijazah) }}" alt="Ijazah">
    <p><strong>Raport:</strong></p>
    <img src="{{ asset('storage/' . $pendaftarDokumen->raport) }}" alt="Raport">
</body>
</html>
