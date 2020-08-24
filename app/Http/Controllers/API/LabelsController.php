<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabelRequest;
use App\Http\Requests\MoveBirthdaysRequest;
use App\Label;
use App\LabelMapping;
use Carbon\Carbon;
use App\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelsController extends Controller
{
    public function create(LabelRequest $request){
        $user = Auth::user();
        $isLabelExists = Label::whereLabelName($request->label_name)->whereCreatedBy($user->id)->first();
        if($isLabelExists != null){
            return response()->json(['errors'=>['label'=>['Label '.$request->label_name.' is already exists']],'label'=>$isLabelExists],422);
        }
        $userId = Auth::user()->id;
        $labelModel = new Label;
        $labelModel->fill($request->all());
        $labelModel->created_by = $userId;
        $labelModel->save();
        $reminderModel = new Reminder;
        $reminderModel->label_id = $labelModel->id;
        $reminderModel->title = 'Day of Occasion';
        $reminderModel->time = '10:00 AM';
        $reminderModel->user_id = $userId;
        $reminderModel->is_manual = 0;
        $reminderModel->tone = 'happy_birthday.mpeg';
        $reminderModel->save();
        $labelsModel = Label::whereCreatedBy($userId)->orWhere('created_by',0)->get();
        return response()->json(['errors'=>null,'message'=>'Label created successfully!','label'=>$labelsModel]);
    }

    public function getLabels(){
        $labelsModel = Label::with(['birthdays.labels'])->whereCreatedBy(Auth::user()->id)->orWhere('created_by',0)->get();
        $labelsArray = [];
        foreach($labelsModel as $k => $label){
            $labelsArray[$k] = $label->toArray();
            if(!$label->birthdays->isEmpty()){
                $toSort = $label->birthdays->toArray();
                usort($toSort,function($a,$b){
                    $date1 = explode('-',$a['birth_date']);
                    $date2 = explode('-',$b['birth_date']);
                    if(isset($date1[2]) && isset($date2[2])){
                        if(Carbon::parse($a['birth_date'])->format('m-d') == Carbon::parse($b['birth_date'])->format('m-d')){return 0;}
                        return (Carbon::parse($a['birth_date'])->format('m-d') < Carbon::parse($b['birth_date'])->format('m-d'))? -1:1;
                    }elseif(isset($date1[2]) && !isset($date2[2])){
                        if(Carbon::parse($a['birth_date'])->format('m-d') == Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d')){return 0;}
                        return (Carbon::parse($a['birth_date'])->format('m-d') < Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d'))? -1:1;
                    }elseif(!isset($date1[2]) && isset($date2[2])){
                        if(Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') == Carbon::parse($b['birth_date'])->format('m-d')){return 0;}
                        return (Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') < Carbon::parse($b['birth_date'])->format('m-d'))? -1:1;
                    }elseif(!isset($date1[2]) && !isset($date2[2])){
                        if(Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') == Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d')){return 0;}
                        return (Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') < Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d'))? -1:1;
                    }
                });
                $low = collect($toSort)->filter(function($birthday){
                    return (Carbon::parse($birthday['birthday'])->format('Y') == Carbon::today()->format('Y'));
                })->values()->toArray();
                $high = collect($toSort)->filter(function($birthday){
                    return (Carbon::parse($birthday['birthday'])->format('Y') > Carbon::today()->format('Y'));
                })->values()->toArray();
                $labelsArray[$k]['birthdays'] = array_merge($low,$high);
            }
        }
        return response()->json(['errors'=>null,'labels'=>$labelsArray]);
    }

    public function destroy($id){
        $labelModel = Label::whereCreatedBy(Auth::user()->id)->find($id);
        if($labelModel != null){
            $labelModel->delete();
            return response()->json(['errors'=>null,'message'=>'Label deleted successfully!']);
        }else{
            return response()->json(['errors'=>'Something went wrong!','message'=>'Unable to delete the label.'],422);
        }
    }

    public function update(Request $request, $id){
        $requestData = array_filter($request->all(), function($item){
            return $item != null;
        });
        $labelModel = Label::whereCreatedBy(Auth::user()->id)->find($id);
        if($labelModel != null){
            $labelModel->fill($requestData);
            $labelModel->save();
            return response()->json(['errors'=>null,'message'=>'Label updated successfully!','label'=>$labelModel]);
        }else{
            return response()->json(['errors'=>['label'=>['Unable to update the label']],'message'=>'Unable to update the label'],422);
        }
    }

    public function labelCounts(){
        $labelModel = LabelMapping::with(['label'])->whereUserId(Auth::user()->id)->get();
        $labelCountArray = [];
        foreach($labelModel->groupBy('label_id') as $key => $label){
            $labelCountArray[$label[0]->label->label_name] = $label->count();
        }
        return response()->json(['errors'=>null,'labels'=>$labelCountArray]);
    }

    public function makeLabelEmpty($id){
        $user = Auth::user();
        LabelMapping::where(['user_id'=>$user->id,'label_id'=>$id])->update(['label_id'=>2]);
        return response()->json(['errors'=>null,'message'=>'Label refreshed successfully!']);
    }

    public function moveBirthdays(MoveBirthdaysRequest $request){
        LabelMapping::whereIn('birthday_id',$request->birthdays)
            ->update(['label_id'=>$request->label_id]);
        return response()->json(['errors'=>null,'message'=>'Birthday moved successfully!']);
    }
}
