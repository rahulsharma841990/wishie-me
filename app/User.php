<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','first_name','last_name','country_code','phone','gender','profile_image',
        'phone','country_code','facebook_id','gmail_id','twitter_id','apple_id','username','dob','device_token','bio',
        'header_image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getProfileImageAttribute($value){
        if($value != null){
            return url(route('public-image',['disk'=>'profile_images','image'=>$value]));
        }else{
            return null;
        }
    }

    public function getHeaderImageAttribute($value){
        if($value != null){
            return url(route('public-image',['disk'=>'profile_images','image'=>$value]));
        }else{
            return null;
        }
    }

    public function setDobAttribute($value){
        $this->attributes['dob'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function getDobAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getPhoneAttribute($value){
        return (string)$value;
    }
}
