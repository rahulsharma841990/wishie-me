<?php

namespace App\Http\Controllers\API;

use App\Friend;
use App\Http\Controllers\Controller;
use App\SavedVideosMapping;
use App\Video;
use App\VideoSharingMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoShareController extends Controller
{
    public function uploadVideo(Request $request){
        if($request->has('video') && $request->has('thumbnail')){

            $image = $request->thumbnail;
            preg_match("/data:image\/(.*?);/",$image,$image_extension);
            $image = preg_replace('/data:image\/(.*?);base64,/','',$image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'image_' . time() . '.' . $image_extension[1];
            Storage::disk('thumbs')->put($imageName,base64_decode($image));

            $videoExt = $request->file('video')->getClientOriginalExtension();
            $videoName = Str::random(20).'.'.$videoExt;
            Storage::disk('video')->put($videoName,\File::get($request->file('video')));
            $videoModel = new Video;
            $videoModel->video = $videoName;
            $videoModel->user_id = Auth::user()->id;
            $videoModel->video_thumbnail = $imageName;
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
        $savedVideos = SavedVideosMapping::with('video')->where(['user_id'=>$user->id])->get();
        $savedVideos = $savedVideos->map(function($video){
            return $video->video;
        });
        return response(['errors'=>null,'message'=>'Videos collected successfully!','drafted_videos'=>$draftedVideos,
            'saved_videos'=>$savedVideos]);
    }

    public function saveVideoToMyVideos(Request $request){
        if($request->has('video_id') && $request->has('publisher_id')){
            $user = Auth::user();
            $savedVideoMapping = SavedVideosMapping::firstOrNew(['video_id'=>$request->video_id,'user_id'=>$user->id,
                'publisher_id'=>$request->publisher_id]);
            $savedVideoMapping->video_id = $request->video_id;
            $savedVideoMapping->publisher_id = $request->publisher_id;
            $savedVideoMapping->user_id = $user->id;
            $savedVideoMapping->save();
            return response()->json(['errors'=>null,'message'=>'Video saved to my profile successfully!']);
        }else{
            return response()->json(
                ['errors'=>[
                                'video_id'=>['Video id is required'],
                                'publisher_id' => ['Publisher id is required']
                            ],
                'message'=>'Required data is missing']);
        }
    }

    public function publishedVideos(Request $request){
        $publishedVideoArray = [];
        $user = Auth::user();
        $myPublishedVideos = Video::where(['user_id'=>$user->id,'is_published'=>1])->get();
        $myFriends = Friend::with(['friend'])->whereUserId($user->id)->get();
        $myFriends = $myFriends->map(function($query){
            return $query->friend;
        });
        $publishedVideoArray = $myPublishedVideos->toArray();
        $friendsVideos = Video::whereIn('user_id',$myFriends->pluck('id'))->where(['is_published'=>1])->get();
        foreach($friendsVideos as $k => $video){
            $publishedVideoArray[] = $video->toArray();
        }
        return response()->json(['errors'=>null,'message'=>'Feeds collected successfully!','feeds'=>$publishedVideoArray]);

    }
}
