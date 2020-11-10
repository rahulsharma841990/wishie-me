<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BirthdayReminder extends Model
{
    protected $fillable = ['birthday_id','reminder_id','title','days_before','time','tone','user_id','is_manual','is_enable'];

    public function birthdays(){
        return $this->belongsTo(Birthday::class,'birthday_id','id');
    }

    public function reminder(){
        return $this->belongsTo(Reminder::class, 'reminder_id','id');
    }
}
