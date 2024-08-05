<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            return response()->json(['error' => $otp2],401);
        }
    }
}
