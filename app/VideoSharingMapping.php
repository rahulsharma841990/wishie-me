<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoSharingMapping extends Model
{
    protected $fillable = ['user_id','video_id','share_with'];
}
