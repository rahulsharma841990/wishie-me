<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoLike extends Model
{
    protected $fillable = ['video_id','user_id','publisher_id'];
}
