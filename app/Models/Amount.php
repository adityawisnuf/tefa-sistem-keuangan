<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Amount extends Model
{
    protected $table = 'amount';
    protected $fillable = ['paymentAmount'];

    // Accessor untuk format `updated_at` menjadi dd/mm/yyyy saat ditampilkan
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    // Accessor untuk format `paymentAmount` menjadi 1.000
    // public function getPaymentAmountAttribute($value)
    // {
    //     return number_format($value, 0, ',', '.');
    // }
}

