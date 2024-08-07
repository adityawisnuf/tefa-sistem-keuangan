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


class EmailVerificationController extends Controller
{
    private $otp;

    public function __construct(){
        $this->otp = new Otp;
    }
    public function email_verification(EmailVerificationRequest $request){
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if(!$otp2->status){
            return response()->json(['error' => $otp2], 401);
        }
        $pendaftar = Email::where('email', $request->email);
        $success['success'] = true;
        return response()->json($success,200);
    }

    
    public function sendEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);
    
        $email = $request->input('email');
    
        try {
            $pendaftar = Email::where('email', $email)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $pendaftar = Email::create(['email' => $email]);
        }
    
        
    
        $pendaftar->notify(new EmailVerificationNotification());
    
        return response()->json(['message' => 'Email verifikasi telah dikirim'], 200);
    }
}
