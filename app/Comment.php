<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['video_id','user_id','comment','publisher_id'];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
