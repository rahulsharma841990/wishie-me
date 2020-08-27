<?php

namespace App\Http\Controllers\API;

use App\BirthdayReminder;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReminderRequest;
use App\Label;
use App\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemindersController extends Controller
{
    public function saveReminder(ReminderRequest $request){
        $user = Auth::user();
        $reminderModel = new Reminder;
        $reminderModel->fill($request->all());
        $reminderModel->user_id = $user->id;
        $reminderModel->is_manual = 1;
        $reminderModel->save();
        $labelModel = Label::with(['birthdays'])->find($request->label_id);
        foreach($labelModel->birthdays as $key => $birthday){
            $birthdayReminderModel = new BirthdayReminder;
            $birthdayReminderModel->birthday_id = $birthday->id;
            $birthdayReminderModel->reminder_id = $reminderModel->id;
            $birthdayReminderModel->title = $request->title;
            $birthdayReminderModel->days_before = $request->days_before;
            $birthdayReminderModel->time = $request->time;
            $birthdayReminderModel->tone = $request->tone;
            $birthdayReminderModel->user_id = $user->id;
            $birthdayReminderModel->is_manual = 0;
            $birthdayReminderModel->save();
        }
        $reminder = Reminder::with(['label.birthdays'])->find($reminderModel->id);
        return response()->json(['errors'=>null,'message'=>'Reminder saved successfully!','reminder'=>$reminder]);
    }

    protected function uploadTone($request){
        $toneFile = $request->file('tone');
        $fileExtension = $request->file('tone')->getClientOriginalExtension();
        $fileName = 'tone_'.Str::random(15).'_file.'.$fileExtension;
        Storage::disk('reminders')->put($fileName,\File::get($toneFile));
        return $fileName;
    }

    public function getReminders(){
        $user = Auth::user()->id;
        $labels = Label::with(['reminders'])->where('created_by',0)->orWhere('created_by',$user)->get();
        return response()->json(['errors'=>null,'message'=>'Reminders collected successfully!',
            'reminders'=>$labels->toArray()]);
    }

    public function updateReminder(Request $request,$id){
        $reminderModel = Reminder::find($id);
        $reminderModel->fill($request->all());
        $reminderModel->save();
        return response()->json(['errors'=>null,'message'=>'Reminder updated successfully!',
            'reminder'=>$reminderModel->toArray()]);
    }

    public function deleteReminder($reminderId){
        $user = Auth::user()->id;
        Reminder::where(['user_id'=>$user,'id'=>$reminderId])->delete();
        BirthdayReminder::where(['reminder_id'=>$reminderId])->delete();
        return response()->json(['errors'=>null,'message'=>'Reminder deleted successfully!']);
    }

    public function enableDisable($reminder_id,$status){
        Reminder::where(['id'=>$reminder_id])->update(['is_enable'=>$status]);
        BirthdayReminder::where(['reminder_id'=>$reminder_id])->update(['is_enable'=>$status]);
        return response()->json(['errors'=>null,'message'=>'Reminder status update successfully!']);
    }
}
