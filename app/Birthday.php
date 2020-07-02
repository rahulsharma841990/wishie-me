<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Birthday extends Model
{
    protected $fillable = ['image','first_name','last_name','birthday','email','mobile','note','created_by'];

    public function getImageAttribute($value){
        return url(route('public-image',['disk'=>'birthday','image'=>$value]));
    }

    public function labels(){
        return $this->hasManyThrough(Label::class,LabelMapping::class,'birthday_id','id','id','label_id');
    }


}
