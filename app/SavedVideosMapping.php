<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SavedVideosMapping extends Model
{
    protected $fillable = ['video_id','user_id','publisher_id'];

    public function video(){
        return $this->belongsTo(Video::class,'video_id','id');
    }
}
