<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['to_user_id','notification','is_read'];
}
