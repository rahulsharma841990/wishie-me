<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['video','user_id','is_draft','is_published','video_thumbnail','type_of_wishie'];

    public function getVideoAttribute($value){
        if($value != null){
            return url(route('public-video',['disk'=>'video','file'=>$value]));
        }else{
            return null;
        }
    }

    public function getVideoThumbnailAttribute($value){
        if($value != null){
            return url(route('video-thumb',['disk'=>'thumbs','file'=>$value]));
        }else{
            return null;
        }
    }

    public function videoShare(){
        return;
    }
}
