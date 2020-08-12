<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ToneController extends Controller
{
    public function tone($disk,$file){
        return Storage::disk($disk)->response($file);
    }
}
