<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Label extends Model
{
    protected $fillable = ['label_name','label_color','created_by'];

    protected $appends = ['total_counts'];

    public function label_count(){
        return $this->hasMany(LabelMapping::class,'label_id','id');
    }

    public function getTotalCountsAttribute(){
        return $this->hasMany(LabelMapping::class)->whereUserId(Auth::user()->id)->count();
    }
}
