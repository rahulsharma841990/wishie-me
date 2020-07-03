<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Birthday extends Model
{
    protected $fillable = ['image','first_name','last_name','birthday','email','mobile','note','created_by'];

    protected $appends = ['birth_date','days_left_or_before','turned_age'];

    public function getImageAttribute($value){
        return url(route('public-image',['disk'=>'birthday','image'=>$value]));
    }

    public function labels(){
        return $this->hasManyThrough(Label::class,LabelMapping::class,'birthday_id','id','id','label_id');
    }

    public function getBirthdayAttribute($value){
        $this->attributes['birth_date'] = $value;
        return Carbon::parse($value)->format('M').' '.Carbon::today()->format('Y');
    }

    public function getBirthDateAttribute($value){
        return $this->attributes['birth_date'];
    }

    public function getDaysLeftOrBeforeAttribute($value){
        $dob = Carbon::createFromFormat('Y-m-d',$this->attributes['birth_date'])->format('m-d');
        $dob = Carbon::createFromFormat('m-d',$dob)->format('Y-m-d');
        return Carbon::parse($dob)->diff(Carbon::today())->days;
    }

    public function getTurnedAgeAttribute(){
        return Carbon::today()->diff(Carbon::parse($this->attributes['birth_date']))->y;
    }

}
