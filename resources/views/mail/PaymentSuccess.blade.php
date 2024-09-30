<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil</title>
    <!-- Fonts Satoshi -->
    <link href="https://fonts.googleapis.com/css2?family=Satoshi:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f2f3f5;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            font-family: 'Satoshi', sans-serif;
        }

        header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
            gap: 1rem;
        }

        img {
            max-height: 3rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: bold;
        }

        div {
            background-color: white;
            max-width: 50%;
            border-radius: 0.5rem;
            padding: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sapaan {
            font-size: 1.3rem;
            font-weight: 700;
        }

        @media only screen and (max-width: 768px) {
            div {
                max-width: 90%;
            }

            img {
                max-height: 2rem;
            }

            h1 {
                font-size: 1.5rem;
                font-weight: 700;
            }
        }
    </style>
</head>

<body>
    <header>
        <img src="{{ $logo_sekolah }}" alt="{{ $nama_sekolah }}">
        <h1>{{ $nama_sekolah }}</h1>
    </header>
    <div>
        <p class="sapaan">Halo {{ $username }}</p>
        <br>
        <p>Pembayaran Anda berhasil.</p>
        <p>Di bawah ini adalah informasi mengenai transaksi Anda:</p>
        <br>

        <p>Nama Pembayaran: {{ $payment_name }}</p>
        <p>Kode Pembayaran: {{ $ds_code }}</p>
        <p>Kode Transaksi: {{ $merchant_order_id }}</p>
        <p>Nominal: Rp. {{ number_format($nominal, 0, ',', '.') }}</p>
        <p>Atas Nama: {{ $customer_name }}</p>
        <p>Metode Pembayaran: {{ $payment_method }}</p>
        <p>Waktu: {{ $payment_time }}</p>
        <p>Status: {{ $payment_status }}</p>
        <br>

        <p>Terima kasih telah melakukan pembayaran. Jika Anda menemukan kendala dalam transaksi ini, silahkan hubungi
            pihak sekolah.</p>
        <p>Hormat Kami,</p>
        <br>
        <p>Pihak Sekolah</p>
    </div>
</body>

</html>
