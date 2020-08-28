<?php

namespace App\Console\Commands;

use App\BirthdayReminder;
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
        $birthdayReminders = BirthdayReminder::with(['birthdays.user'])->get();
        foreach($birthdayReminders as $key => $reminder){
            if($reminder->days_before != null){
                $explodedDate = explode('-',$reminder->birthdays->toArray()['birth_date']);
                if(!isset($explodedDate[2])){
                    $birthDate = Carbon::createFromFormat('m-d',$reminder->birthdays->toArray()['birth_date']);
                }else{
                    $birthDate = Carbon::parse($reminder->birthdays->toArray()['birth_date']);
                }
                if($reminder->days_before == 'Day of Occasion'){
                    if(Carbon::now()->format('m-d') == $birthDate->format('m-d')){
                        $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
                            'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id];
                        $this->sendNotification($notificationArray);
                    }
                }else{
                    $explodedVal = explode(' ',$reminder->days_before);
                    if($explodedVal[1] == 'day' || $explodedVal[1] == 'days'){
                        if(Carbon::now()->format('m-d') == $birthDate->subDay($explodedVal[0])->format('m-d')){
                            $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
                                'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id];
                            $this->sendNotification($notificationArray);
                        }
                    }
                    if($explodedVal[1] == 'week' || $explodedVal[1] == 'weeks'){
                        if(Carbon::now()->format('m-d') == $birthDate->subWeeks($explodedVal[0])->format('m-d')){
                            $notificationArray = ['name'=>$reminder->birthdays->first_name,$reminder->birthdays->last_name,
                                'token'=>$reminder->birthdays->user->device_token,'birthday_id'=>$reminder->birthdays->id];
                            $this->sendNotification($notificationArray);
                        }
                    }
                }
            }
        }
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
