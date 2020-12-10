<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function video($disk,$file){
        return Storage::disk($disk)->response($file);
    }

    public function thumb($disk,$file){
        return Storage::disk($disk)->response($file);
    }

}
