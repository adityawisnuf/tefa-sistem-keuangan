<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Models\Ppdb;
use Illuminate\Http\Request;

class PpdbController extends Controller
{
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created pendaftar in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function store(Request $request)
     {


        $ppdb = Ppdb::create([
            'status' => 1,
        ]);
        
         return response()->json([
             'message' => 'Anda telah berhasil mendaftar!',
             'pendaftar' => $ppdb
         ], 201);
     }
}
