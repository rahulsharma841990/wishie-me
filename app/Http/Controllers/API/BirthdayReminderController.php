<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\BirthdayReminder;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBirthdayReminderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BirthdayReminderController extends Controller
{
    public function getBirthdayReminders($birthday_id){
        $birthdayReminderModel = BirthdayReminder::whereBirthdayId($birthday_id)->get();
        return response()->json(['errors'=>null,'message'=>'Birthday reminders collected successfully!',
            'reminders'=>$birthdayReminderModel->toArray()]);
    }

    public function create(CreateBirthdayReminderRequest $request, $birthday_id){
        $user = Auth::user();
        $birthday = Birthday::with(['labels.reminders'])->find($birthday_id);
        $birthdayReminderModel = new BirthdayReminder;
        $birthdayReminderModel->reminder_id = $birthday->labels[0]->reminders[0]->id;
        $birthdayReminderModel->birthday_id = $birthday_id;
        $birthdayReminderModel->title = $request->title;
        $birthdayReminderModel->days_before = $request->days_before;
        $birthdayReminderModel->time = $request->time;
        $birthdayReminderModel->tone = $request->tone;
        $birthdayReminderModel->user_id = $user->id;
        $birthdayReminderModel->is_manual = 1;
        $birthdayReminderModel->save();

        return response()->json(['errors'=>null,'message'=>'Reminder created successfully!','reminder'=>$birthdayReminderModel]);
    }

    public function enableDisableReminder($birthday_reminder_id, Request $request){
        $user = Auth::user();
        $rules = [
            'enable_or_disable' => 'required'
        ];
        $request->validate($rules);
        BirthdayReminder::where('id',$birthday_reminder_id)->where('user_id',$user->id)
            ->update(['is_enable'=>$request->enable_or_disable]);
        return response()->json(['errors'=>null,'message'=>'Reminder status updated successfully!']);
    }

    public function deleteBirthdayReminder($birthday_reminder_id){
        $user = Auth::user();
        BirthdayReminder::where(['user_id'=>$user->id,'id'=>$birthday_reminder_id])->delete();
        return response()->json(['errors'=>null,'message'=>'Reminder deleted successfully!']);
    }
}
