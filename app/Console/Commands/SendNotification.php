<?php

namespace App\Console\Commands;

use App\BirthdayReminder;
use App\NotificationLog;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send the notification to all users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $birthdayReminders = BirthdayReminder::where(['is_notified'=>0])->with(['birthdays' => function($model){
//            return $model->with(['user','labels.label_reminders']);
//        },'reminder'])->get();
        $notificationLogArray = [];
//        foreach($birthdayReminders as $key => $reminder){
//            if($reminder->is_enable == 1){
//                if($reminder->days_before != null){
//                    if($reminder->birthdays != null){
//                        $explodedDate = explode('-',$reminder->birthdays->toArray()['birth_date']);
//                        if(!isset($explodedDate[2])){
//                            $birthDate = Carbon::createFromFormat('m-d',$reminder->birthdays->toArray()['birth_date']);
//                        }else{
//                            $birthDate = Carbon::parse($reminder->birthdays->toArray()['birth_date']);
//                        }
//                        if($reminder->days_before == 'Day of Occasion'){
//                            if(Carbon::now()->format('m-d') == $birthDate->format('m-d') && Carbon::now()->format('h:i A') >= $reminder->time){
//                                $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                    'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                    'user_id'=>$reminder->user_id];
//                                $this->sendNotification($notificationArray);
//                                $notificationLogArray[] = $notificationArray;
//                                $reminder->is_notified = 1;
//                                $reminder->save();
//                            }
//                        }else{
//                            $explodedVal = explode(' ',$reminder->days_before);
//                            if($explodedVal[1] == 'day' || $explodedVal[1] == 'days'){
//                                if(Carbon::now()->format('m-d') == $birthDate->subDay($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $reminder->time){
//                                    $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                        'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                        'user_id'=>$reminder->user_id];
//                                    $this->sendNotification($notificationArray);
//                                    $notificationLogArray[] = $notificationArray;
//                                    $reminder->is_notified = 1;
//                                    $reminder->save();
//                                }
//                            }
//                            if($explodedVal[1] == 'week' || $explodedVal[1] == 'weeks'){
//                                if(Carbon::now()->format('m-d') == $birthDate->subWeeks($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $reminder->time){
//                                    $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                        'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                        'user_id'=>$reminder->user_id];
//                                    $this->sendNotification($notificationArray);
//                                    $notificationLogArray[] = $notificationArray;
//                                    $reminder->is_notified = 1;
//                                    $reminder->save();
//                                }
//                            }
//                        }
//                    }
//                }
//            }elseif($reminder->birthdays != null){
//                $userId = $reminder->birthdays->created_by;
//                $labelReminders = $reminder->birthdays->labels->first()->label_reminders->where('user_id',$userId);
//                foreach($labelReminders as $key => $labelReminder){
//                    if($labelReminder->days_before != null){
//                        $explodedDate = explode('-',$reminder->birthdays->toArray()['birth_date']);
//                        if(!isset($explodedDate[2])){
//                            $birthDate = Carbon::createFromFormat('m-d',$reminder->birthdays->toArray()['birth_date']);
//                        }else{
//                            $birthDate = Carbon::parse($reminder->birthdays->toArray()['birth_date']);
//                        }
//                        if($labelReminder->days_before == 'Day of Occasion'){
//                            if(Carbon::now()->format('m-d') == $birthDate->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
//                                $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                    'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                    'user_id'=>$reminder->user_id];
//                                $this->sendNotification($notificationArray);
//                                $notificationLogArray[] = $notificationArray;
//                                $reminder->is_notified = 1;
//                                $labelReminder->is_notified = 1;
//                                $labelReminder->save();
//                                $reminder->save();
//                            }
//                        }else{
//                            $explodedVal = explode(' ',$labelReminder->days_before);
//                            if($explodedVal[1] == 'day' || $explodedVal[1] == 'days'){
//                                if(Carbon::now()->format('m-d') == $birthDate->subDay($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
//                                    $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                        'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                        'user_id'=>$reminder->user_id];
//                                    $this->sendNotification($notificationArray);
//                                    $notificationLogArray[] = $notificationArray;
//                                    $reminder->is_notified = 1;
//                                    $labelReminder->is_notified = 1;
//                                    $labelReminder->save();
//                                    $reminder->save();
//                                }
//                            }
//                            if($explodedVal[1] == 'week' || $explodedVal[1] == 'weeks'){
//                                if(Carbon::now()->format('m-d') == $birthDate->subWeeks($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
//                                    $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
//                                        'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id,
//                                        'user_id'=>$reminder->user_id];
//                                    $this->sendNotification($notificationArray);
//                                    $notificationLogArray[] = $notificationArray;
//                                    $reminder->is_notified = 1;
//                                    $labelReminder->is_notified = 1;
//                                    $labelReminder->save();
//                                    $reminder->save();
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }

        $labelReminders = Reminder::with(['birthdays'])->where(['is_notified'=>0])->get();
        foreach($labelReminders as $key => $labelReminder){
            if($labelReminder->days_before != null){
                foreach($labelReminder->birthdays->where('created_by',$labelReminder->user_id) as $key => $birthday){
                    $explodedDate = explode('-',$birthday->toArray()['birth_date']);
                    if(!isset($explodedDate[2])){
                        $birthDate = Carbon::createFromFormat('m-d',$birthday->toArray()['birth_date']);
                    }else{
                        $birthDate = Carbon::parse($birthday->toArray()['birth_date']);
                    }

                    if($labelReminder->days_before == 'Day of Occasion'){
                        if(Carbon::now()->format('m-d') == $birthDate->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
                            $notificationArray = ['name'=>$birthday->first_name,$birthday->last_name,
                                'token'=>$birthday->user->device_token,'birthday_id'=>$birthday->id,
                                'user_id'=>$labelReminder->user_id];
                            $this->sendNotification($notificationArray);
                            $notificationLogArray[] = $notificationArray;
                            $labelReminder->is_notified = 1;
                            $labelReminder->save();
                        }
                    }else{
                        $explodedVal = explode(' ',$labelReminder->days_before);
                        if($explodedVal[1] == 'day' || $explodedVal[1] == 'days'){
                            if(Carbon::now()->format('m-d') == $birthDate->subDay($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
                                $notificationArray = ['name'=>$birthday->first_name,$birthday->last_name,
                                    'token'=>$birthday->user->device_token,'birthday_id'=>$birthday->id,
                                    'user_id'=>$labelReminder->user_id];
                                $this->sendNotification($notificationArray);
                                $notificationLogArray[] = $notificationArray;
                                $labelReminder->is_notified = 1;
                                $labelReminder->save();
                            }
                        }
                        if($explodedVal[1] == 'week' || $explodedVal[1] == 'weeks'){
                            if(Carbon::now()->format('m-d') == $birthDate->subWeeks($explodedVal[0])->format('m-d') && Carbon::now()->format('h:i A') >= $labelReminder->time){
                                $notificationArray = ['name'=>$birthday->first_name,$birthday->last_name,
                                    'token'=>$birthday->user->device_token,'birthday_id'=>$birthday->id,
                                    'user_id'=>$labelReminder->user_id];
                                $this->sendNotification($notificationArray);
                                $notificationLogArray[] = $notificationArray;
                                $labelReminder->is_notified = 1;
                                $labelReminder->save();
                            }
                        }
                    }

                }
            }
        }


        $insertArray = [];
        foreach($notificationLogArray as $k => $notification){
            $insertArray[] = [
                'to_user_id' => $notification['user_id'],
                'notification'=>$notification['name'].' birthday. Wish them Happy birthday.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        NotificationLog::insert($insertArray);
    }

    protected function sendNotification($deviceTokens){
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $notificationBuilder = new PayloadNotificationBuilder('Wishi Me');
        $notificationBuilder->setBody('It\'s '.$deviceTokens['name'].' birthday. Wish them Happy birthday.')
            ->setSound('default')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['birthday_id' => $deviceTokens['birthday_id']]);
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $token = $deviceTokens['token'];
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        return response()->json(['errors'=>null,'number_success'=>$downstreamResponse->numberSuccess(),
            'number_failure'=>$downstreamResponse->numberFailure(),
            'number_modification'=>$downstreamResponse->numberModification()]);
    }
}
