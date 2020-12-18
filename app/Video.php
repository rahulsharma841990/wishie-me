<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        return $this->belongsTo(VideoSharingMapping::class,'id','video_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function comments(){
        return $this->hasMany(Comment::class,'video_id','id');
    }

    public function didILike(){
        return $this->belongsTo(VideoLike::class,'id','video_id')
            ->where('user_id',Auth::user()->id);
    }

}
