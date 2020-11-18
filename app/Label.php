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
        $relation = $this->hasMany(LabelMapping::class);
        if(Auth::check()){
            return $relation->whereUserId(Auth::user()->id)->count();
        }else{
            return $relation->count();
        }
    }

    public function birthdays(){
        $relation = $this->hasManyThrough(Birthday::class,LabelMapping::class,'label_id',
            'id','id','birthday_id');
        if(Auth::check()){
            return $relation->where('user_id',Auth::user()->id);
        }else{
            return $relation;
        }
    }


    public function getBirthdayCountsAttribute(){
        return $this->birthdays()->count();
    }

    public function reminders(){
        return $this->hasMany(Reminder::class,'label_id','id')->where('user_id',Auth::user()->id);
    }

    public function label_reminders(){
        return $this->hasMany(Reminder::class,'label_id','id');
    }
}
