<?php

namespace App;

use Carbon\Carbon;
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

    public function getBirthdayAttribute($value){
        $this->birth_date = $value;
        return Carbon::parse($value)->format('M').' '.Carbon::today()->format('Y');
    }
}
