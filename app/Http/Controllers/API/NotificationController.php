<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Http\Requests\ReadNotificationRequest;
use App\NotificationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class NotificationController extends Controller
{
    public function sendNotification(NotificationRequest $request){
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($request->title);
        $notificationBuilder->setBody($request->body)
            ->setSound('default')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = $request->device_token;

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        return response()->json(['errors'=>null,'number_success'=>$downstreamResponse->numberSuccess(),
            'number_failure'=>$downstreamResponse->numberFailure(),
            'number_modification'=>$downstreamResponse->numberModification()]);
    }

    public function getNotifications(){
        $user = Auth::user();
        $notificationModel = NotificationLog::where(['to_user_id'=>$user->id])
            ->select(['id','to_user_id','notification','notify_date','is_read','created_at','updated_at',DB::raw('date(notify_date) as date')])
            ->where(DB::raw('date(notify_date)'),'>',Carbon::now()->subDays(7)->format('Y-m-d'))
            ->get();
        dd($notificationModel);
        return response()->json(['errors'=>null,'notifications'=>$notificationModel->groupBy('date'),
            'message'=>'Notifications collected successfully!']);
    }

    public function setRead(ReadNotificationRequest $request){
        $user = Auth::user();
        NotificationLog::where(['id'=>$request->notification_id,'to_user_id'=>$user->id])
            ->udpate(['is_read'=>1]);
        return response()->json(['errors'=>null,'message'=>'Notification update successfully!']);
    }
}
