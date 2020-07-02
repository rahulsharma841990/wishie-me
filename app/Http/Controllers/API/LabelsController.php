<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabelRequest;
use App\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelsController extends Controller
{
    public function create(LabelRequest $request){
        $isLabelExists = Label::whereLabelName($request->label_name)->first();
        if($isLabelExists != null){
            return response()->json(['errors'=>['label'=>['Label '.$request->label_name.' is already exists']],'label'=>$isLabelExists]);
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
        $labelsModel = Label::whereCreatedBy(Auth::user()->id)->orWhere('created_by',0)->get();
        return response()->json(['errors'=>null,'labels'=>$labelsModel]);
    }

    public function destroy($id){
        $labelModel = Label::whereCreatedBy(Auth::user()->id)->find($id);
        if($labelModel != null){
            $labelModel->delete();
            return response()->json(['errors'=>null,'message'=>'Label deleted successfully!']);
        }else{
            return response()->json(['errors'=>'Something went wrong!','message'=>'Unable to delete the label.']);
        }
    }
}
