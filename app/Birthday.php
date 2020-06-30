<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Birthday extends Model
{
    protected $fillable = ['image','first_name','last_name','label','birthday','email','mobile','note','created_by'];

    public function getLabelAttribute($value){
        return json_decode($value);
    }

    public function getImageAttribute($value){
        return url(route('public-image',['disk'=>'birthday','image'=>$value]));
    }
}
