<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Video;
use App\VideoSharingMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoShareController extends Controller
{
    public function uploadVideo(Request $request){
        if($request->has('video')){
            $videoExt = $request->file('video')->getClientOriginalExtension();
            $videoName = Str::random(20).'.'.$videoExt;
            Storage::disk('video')->put($videoName,\File::get($request->file('video')));
            $videoModel = new Video;
            $videoModel->video = $videoName;
            $videoModel->user_id = Auth::user()->id;
            $videoModel->is_draft = 1;
            $videoModel->save();
            return response(['errors'=>null,'message'=>'Video uploaded successfully!','video'=>$videoName,'video_id'=>$videoModel->id]);
        }else{
            return response(['errors'=>['video'=>['Please upload the video']],'message'=>'Please upload the video'],422);
        }
    }

    public function shareVideo(Request $request){
        $user = Auth::user();
        $sharingModel = VideoSharingMapping::firstOrNew([
            'user_id'=>$user->id,
            'video_id'=>$request->video_id,
            'share_with'=>$request->share_with]);
        if(!$sharingModel->exists){
            $sharingModel->user_id = $user->id;
            $sharingModel->video_id = $request->video_id;
            $sharingModel->share_with = $request->share_with;
            $sharingModel->save();
            return response(['errors'=>null,'message'=>'Video shared successfully!']);
        }else{
            return response(['errors'=>['video'=>['Video already shared with same user!']],'message'=>'Video shared successfully!']);
        }
    }

    public function listOfVideos(){
        $user = Auth::user();
        $listOfVideos = Video::where(['user_id'=>$user->id])->get();
        $draftedVideos = $listOfVideos->where('is_draft',1);
        $publishedVideos = $listOfVideos->where('is_published',1);
        return response(['errors'=>null,'message'=>'Videos collected successfully!','drafted_videos'=>$draftedVideos,
            'published_videos'=>$publishedVideos]);
    }
}
