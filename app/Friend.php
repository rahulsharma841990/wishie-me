<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class Friend extends Model
{
    protected $fillable = ['user_id','friend_id','is_accepted','is_rejected'];

    public static function sendNotification($user, $fromUser, $message){
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $notificationBuilder = new PayloadNotificationBuilder('New Friend Request');
        $notificationBuilder->setBody($message)
            ->setSound('default')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['from_user' => $fromUser]);
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $token = $user->device_token;
        $notificationLog = new NotificationLog;
        $notificationLog->to_user_id = $user->id;
        $notificationLog->notification = $message;
        $notificationLog->save();
        if($token != null){
            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
            return response()->json(['errors'=>null,'number_success'=>$downstreamResponse->numberSuccess(),
                'number_failure'=>$downstreamResponse->numberFailure(),
                'number_modification'=>$downstreamResponse->numberModification()]);
        }

    }
}
