<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
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

    protected $appends = ['friends_count','is_my_friend','is_friend_request_sent','is_blocked'];

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

    public function friends(){
        return $this->hasMany(Friend::class,'user_id','id');
    }

    public function getFriendsCountAttribute(){
        return $this->friends()->where(['is_accepted'=>1])->count();
    }

    public function friend(){
        return $this->belongsTo(Friend::class,'id','user_id');
    }

    public function myFriends(){
        return $this->hasMany(Friend::class,'user_id','id')->where(['is_accepted'=>1]);
    }

    public function getIsMyFriendAttribute($value){
        if(Auth::check()){
            $isFriend = $this->friend()->where(['friend_id'=>Auth::user()->id,'is_accepted'=>1])->first();
            return ($isFriend == null)?false:true;
        }
    }

    public function getIsFriendRequestSentAttribute(){
        if(Auth::check()) {
            $isFriendRequestSent = $this->friend()->where(['friend_id' => Auth::user()->id])
                ->whereNull('is_accepted')
                ->whereNull('is_rejected')->first();
            return ($isFriendRequestSent == null) ? false : true;
        }
    }

    public function videoSharedWithMe(){
        return $this->hasMany(VideoSharingMapping::class,'share_with','id');
    }

    public function videoSharedByMe(){
        return $this->hasMany(VideoSharingMapping::class,'user_id','id');
    }

    public function getIsBlockedAttribute(){
        $isFriend = $this->friend()->first();
        if($isFriend != null){
            return $this->friend()->first()->is_blocked;
        }else{
            return null;
        }
    }
}
