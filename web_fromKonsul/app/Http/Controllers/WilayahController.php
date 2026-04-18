<?php

namespace App\Http\Controllers;

class WilayahController extends Controller
{
    public function cities()
    {
        return response()->json(config('wilayah_kota.mapping'));
    }
}
