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
        $labelModel = new Label;
        $labelModel->fill($request->all());
        $labelModel->created_by = Auth::user()->id;
        $labelModel->save();
        return response()->json(['errors'=>null,'message'=>'Label created successfully!','label'=>$labelModel]);
    }

    public function getLabels(){
        $labelsModel = Label::whereCreatedBy(Auth::user()->id)->get();
        return response()->json(['errors'=>null,'labels'=>$labelsModel]);
    }

    public function destroy($id){
        $labelModel = Label::whereCreatedBy(Auth::user()->id)->find($id);
        if($labelModel != null){
            $labelModel->delete();
        }
        return response()->json(['errors'=>null,'message'=>'Label deleted successfully!']);
    }
}
