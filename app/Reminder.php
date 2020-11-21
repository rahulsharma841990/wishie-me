<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['label_id','title','days_before','time','tone','user_id','is_manual','is_enable','is_notified'];

    public function label(){
        return $this->belongsTo(Label::class,'label_id','id');
    }

//    public function getToneAttribute($value){
//        if($value != null){
//            return url(route('public-tone',['disk'=>'reminders','file'=>$value]));
//        }
//    }

    public function birthdays(){
        return $this->hasManyThrough(Birthday::class,LabelMapping::class,'label_id','id','label_id','birthday_id');
    }
}
