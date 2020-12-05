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
        $reminderModel->is_notified = 0;
        $reminderModel->save();
        return response()->json(['errors'=>null,'message'=>'Reminder updated successfully!',
            'reminder'=>$reminderModel->toArray()]);
    }

    public function deleteReminder($reminderId){
        $user = Auth::user()->id;
        Reminder::where(['user_id'=>$user,'id'=>$reminderId])->delete();
        return response()->json(['errors'=>null,'message'=>'Reminder deleted successfully!']);
    }

    public function enableDisable($label_id,$status){
        $user = Auth::user();
        $updatedReminder = Reminder::where(['label_id'=>$label_id,'user_id'=>$user->id]);
        $reminderIds = $updatedReminder->get();
        $updatedReminder->update(['is_enable'=>$status]);
        return response()->json(['errors'=>null,'message'=>'Reminder status update successfully!']);
    }

    public function resetReminders(){
        $user = Auth::user();
        Reminder::whereNotIn('label_id',[2,3,4])->where('user_id',$user->id)->delete();
        return response()->json(['errors'=>null,'message'=>'Reminders deleted successfully!']);
    }
}
