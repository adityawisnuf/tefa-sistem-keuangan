<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all()[
            
        ]);
    }
}
