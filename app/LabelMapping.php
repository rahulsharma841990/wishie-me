<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelMapping extends Model
{
    protected $fillable = ['birthday_id','label_id','user_id'];

    public function label(){
        return $this->belongsTo(Label::class,'label_id','id');
    }
}
