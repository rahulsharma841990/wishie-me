<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoSharingMapping extends Model
{
    protected $fillable = ['user_id','video_id','share_with'];

    public function shareWith(){
        return $this->belongsTo(User::class,'share_with','id');
    }

    public function video(){
        return $this->belongsTo(Video::class,'video_id','id');
    }
}
