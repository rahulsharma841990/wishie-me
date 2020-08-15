<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Label extends Model
{
    protected $fillable = ['label_name','label_color','created_by'];

    protected $appends = ['total_counts','birthday_counts'];

    public function label_count(){
        return $this->hasMany(LabelMapping::class,'label_id','id');
    }

    public function getTotalCountsAttribute(){
        return $this->hasMany(LabelMapping::class)->whereUserId(Auth::user()->id)->count();
    }

    public function birthdays(){
        return $this->hasManyThrough(Birthday::class,LabelMapping::class,'label_id',
            'id','id','birthday_id')->where('user_id',Auth::user()->id);
    }

    public function getBirthdayCountsAttribute(){
        return $this->birthdays()->count();
    }

    public function reminders(){
        return $this->hasMany(Reminder::class,'label_id','id')->where('user_id',Auth::user()->id);
    }
}
