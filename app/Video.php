<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['video','user_id','is_draft','is_published'];

    public function getVideoAttribute($value){
        if($value != null){
            return url(route('public-video',['disk'=>'video','file'=>$value]));
        }else{
            return null;
        }
    }
}
