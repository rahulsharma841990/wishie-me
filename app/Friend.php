<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class Friend extends Model
{
    protected $fillable = ['user_id','friend_id','is_accepted','is_rejected','is_blocked'];

    public static function sendNotification($fromUser, $user, $message){
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $notificationBuilder = new PayloadNotificationBuilder('New Friend Request');
        $notificationBuilder->setBody($message)
            ->setSound('default')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['from_user' => json_encode($fromUser)]);
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $token = $fromUser->device_token;
        $notificationLog = new NotificationLog;
        $notificationLog->to_user_id = $fromUser->id;
        $notificationLog->notification = $message;
        $notificationLog->notify_date = date('Y-m-d H:i:s');
        $notificationLog->save();
        if($token != null){
            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
            return response()->json(['errors'=>null,'number_success'=>$downstreamResponse->numberSuccess(),
                'number_failure'=>$downstreamResponse->numberFailure(),
                'number_modification'=>$downstreamResponse->numberModification()]);
        }

    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function friend(){
        return $this->belongsTo(User::class,'friend_id','id');
    }

    public function friend_of(){
        return $this->belongsTo(self::class,'user_id','friend_id');
    }
}
