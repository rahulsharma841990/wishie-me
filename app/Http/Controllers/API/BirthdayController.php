<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\Http\Requests\BirthdayRequest;
use App\Http\Controllers\Controller;
use App\LabelMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BirthdayController extends Controller
{
    public function create(BirthdayRequest $request){
        $requestData = $request->except(['image','label','birthday']);
        $birthdayModel = new Birthday;
        $imageName = $this->uploadFile($request);
        $requestData['image'] = $imageName;
        $requestData['birthday'] = Carbon::parse($request->birthday)->format('Y-m-d');
        $requestData['created_by'] = Auth::user()->id;
        $birthdayModel->fill($requestData);
        $birthdayModel->save();
        $this->saveBirthdayLabels($request,$birthdayModel);
        $birthdays = Birthday::with(['labels'])->whereCreatedBy(Auth::user()->id)->get()->toArray();
        return response()->json(['errors'=>null,'message'=>'Birthday created successfully!','birthdays'=>$birthdays]);
    }

    protected function saveBirthdayLabels($request, $birthdayModel){
        foreach($request->label as $key => $label){
            $labelMappingModel = new LabelMapping;
            $labelMappingModel->birthday_id = $birthdayModel->id;
            $labelMappingModel->label_id = $label;
            $labelMappingModel->user_id = Auth::user()->id;
            $labelMappingModel->save();
        }
        return true;
    }

    private function uploadFile($request){
        $image = $request->input('image');
        preg_match("/data:image\/(.*?);/",$image,$image_extension);
        $image = preg_replace('/data:image\/(.*?);base64,/','',$image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'image_' . time() . '.' . $image_extension[1];
        Storage::disk('birthday')->put($imageName,base64_decode($image));
        return $imageName;
    }

    public function getBirthdays(){
        $birthdayModel = Birthday::with(['labels'])->get()->toArray();
        dd($birthdayModel);
    }
}
