<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PembayaranDuitku;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PembayaranDuitkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PembayaranDuitku::create([
            'merchant_order_id' => 'ORD123456789',
            'reference' => 'REF123456',
            'payment_method' => 'credit_card',
            'transaction_response' => 'Success',
            'callback_response' => 'Callback received successfully',
            'status' => 1,
   ]);

    }
}
