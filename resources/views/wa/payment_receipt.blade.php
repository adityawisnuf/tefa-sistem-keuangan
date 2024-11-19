<!DOCTYPE html>
<html lang="en">
<html>
<head>
    <title>Struk Pembayaran</title>
</head>
<body>
    <h1>{{ $nama_sekolah }}</h1>
        <p>Nama Pembayaran: {{ $payment_name }}</p>
        <p>Kode Pembayaran: {{ $ds_code }}</p>
        <p>Kode Transaksi: {{ $merchant_order_id }}</p>
        <p>Nominal: Rp. {{ number_format($nominal, 0, ',', '.') }}</p>
        <p>Atas Nama: {{ $customer_name }}</p>
        <p>Metode Pembayaran: {{ $payment_method }}</p>
        <p>Waktu: {{ $payment_time }}</p>
        <p>Status: {{ $payment_status }}</p>
        <br>
</body>
</html>
