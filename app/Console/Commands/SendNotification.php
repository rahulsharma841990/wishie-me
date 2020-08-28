<?php

namespace App\Console\Commands;

use App\BirthdayReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $birthdayReminders = BirthdayReminder::with(['birthdays'])->get();
        foreach($birthdayReminders as $key => $reminder){
            $birthDate = Carbon::parse($reminder->birthdays->toArray()['birth_date']);
            $explodedVal = explode(' ',$reminder->days_before);
            if($explodedVal[1] == 'day' || $explodedVal[1] == 'days'){
                if(Carbon::now() == $birthDate->subDay($explodedVal[0])){

                }
            }
            if($explodedVal[1] == 'week' || $explodedVal[1] == 'weeks'){
                if(Carbon::now() == $birthDate->subWeeks($explodedVal[0])){
                    
                }
            }
        }
    }
}
