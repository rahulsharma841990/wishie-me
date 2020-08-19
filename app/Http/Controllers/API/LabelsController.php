<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabelRequest;
use App\Label;
use App\LabelMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelsController extends Controller
{
    public function create(LabelRequest $request){
        $isLabelExists = Label::whereLabelName($request->label_name)->first();
        if($isLabelExists != null){
            return response()->json(['errors'=>['label'=>['Label '.$request->label_name.' is already exists']],'label'=>$isLabelExists],422);
        }
        $userId = Auth::user()->id;
        $labelModel = new Label;
        $labelModel->fill($request->all());
        $labelModel->created_by = $userId;
        $labelModel->save();
        $labelsModel = Label::whereCreatedBy($userId)->orWhere('created_by',0)->get();
        return response()->json(['errors'=>null,'message'=>'Label created successfully!','label'=>$labelsModel]);
    }

    public function getLabels(){
        $labelsModel = Label::with(['birthdays.labels'])->whereCreatedBy(Auth::user()->id)->orWhere('created_by',0)->get();
        return response()->json(['errors'=>null,'labels'=>$labelsModel]);
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
}
