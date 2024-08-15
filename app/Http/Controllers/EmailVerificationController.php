<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\ModelOtp;
use App\Models\Otp as ModelsOtp;
use App\Models\Pendaftar;
use App\Notifications\EmailVerificationNotification;
use Ichtrojan\Otp\Commands\CleanOtps;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Artisan;

class EmailVerificationController extends Controller
{
    private $otp;

    public function __construct(){
        $this->otp = new Otp;
    }
    
    public function email_verification(EmailVerificationRequest $request){
        Artisan::call('otp:clean');
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if(!$otp2->status){
            return response()->json(['error' => $otp2], 401);
        }
        Email::where('email', $request->email)->first();
        $success['success'] = true;
        return response()->json($success,200);
    }

    
public function sendEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);
    
        $email = $request->input('email');
    
        $pendaftar = Email::where('email', $email)->first();
    
        if (!$pendaftar) {
            $pendaftar = new Pendaftar();
            $pendaftar->email = $email;
        }    
        $pendaftar->notify(new EmailVerificationNotification());
    
        return response()->json(['message' => 'Email verifikasi telah dikirim'], 200);
    }
}

