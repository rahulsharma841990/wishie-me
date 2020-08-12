<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['label_id','title','days_before','time','tone','user_id'];

    public function label(){
        return $this->belongsTo(Label::class,'label_id','id');
    }

    public function getToneAttribute($value){
        if($value != null){
            return url(route('public-tone',['disk'=>'reminders','file'=>$value]));
        }
    }
}
