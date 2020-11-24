<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class Friend extends Model
{
    protected $fillable = [];

    public static function sendNotification($user,$message){
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $notificationBuilder = new PayloadNotificationBuilder('New Friend Request');
        $notificationBuilder->setBody('It\'s '.$deviceTokens['name'].' birthday. Wish them Happy birthday.')
            ->setSound('default')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['birthday_id' => $deviceTokens['birthday_id']]);
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $token = $deviceTokens['token'];
        if($token != null){
            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
            return response()->json(['errors'=>null,'number_success'=>$downstreamResponse->numberSuccess(),
                'number_failure'=>$downstreamResponse->numberFailure(),
                'number_modification'=>$downstreamResponse->numberModification()]);
        }
    }
}
