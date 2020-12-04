<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VideoShareController extends Controller
{
    public function uploadVideo(Request $request){
        if($request->has('video')){
            $videoExt = $request->file('video')->getClientOriginalExtension();
            $videoName = Str::random(20).'.'.$videoExt;
            $request->file('video')->move('videos',$videoName);
            $videoModel = new Video;
            $videoModel->video = $videoName;
            $videoModel->user_id = Auth::user()->id;
            $videoModel->save();
            return response(['errors'=>null,'message'=>'Video uploaded successfully!','video'=>$videoName,'video_id'=>$videoModel->id]);
        }else{
            return response(['errors'=>['video'=>['Please upload the video']],'message'=>'Please upload the video'],422);
        }
    }


}
