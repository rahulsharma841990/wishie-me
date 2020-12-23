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
        if($request->has('video') && $request->has('thumbnail') && $request->has('type_of_wishie')){

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
            $videoModel->type_of_wishie = $request->type_of_wishie;
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
            $this->publishVideo($request->video_id,$user->id);
            return response(['errors'=>null,'message'=>'Video shared successfully!']);
        }else{
            return response(['errors'=>['video'=>['Video already shared with same user!']],'message'=>'Video shared successfully!']);
        }
    }

    protected function publishVideo($video_id,$user_id){
        Video::where(['id'=>$video_id,'user_id'=>$user_id])->update(['is_draft'=>null,'is_published'=>1]);
    }

    public function listOfVideos(){
        $user = Auth::user();
        $listOfVideos = Video::where(['user_id'=>$user->id])->get();
        $draftedVideos = $listOfVideos->where('is_draft',1)->values();
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
            if(!$savedVideoMapping->exists){
                $savedVideoMapping->video_id = $request->video_id;
                $savedVideoMapping->publisher_id = $request->publisher_id;
                $savedVideoMapping->user_id = $user->id;
                $savedVideoMapping->save();
                return response()->json(['errors'=>null,'message'=>'Video saved to my profile successfully!']);
            }else{
                $savedVideoMapping->delete();
                return response()->json(['errors'=>null,'message'=>'Video removed from favourite successfully!']);
            }
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
        $myPublishedVideos = Video::with(['videoShare.shareWith','user','comments','didILike','saved_videos.video'])->where(['user_id'=>$user->id,'is_published'=>1])->get();
        $myFriends = Friend::with(['friend'])->whereUserId($user->id)->get();
        $myFriends = $myFriends->map(function($query){
            return $query->friend;
        });
        $myPublishedVideos = $myPublishedVideos->map(function($item){
            if($item['saved_videos'] != null && $item['saved_videos']['video'] != null){
                $item['is_favourite'] = true;
            }else{
                $item['is_favourite'] = false;
            }
            unset($item['saved_videos']);
            if($item->videoShare != null){
                $item['shared_with'] = $item->videoShare->shareWith->toArray();
            }else{
                $item['shared_with'] = null;
            }
            $item['who_shared'] = $item->user->toArray();
            $item['did_i_like'] = ($item->didILike != null)?true:false;
            unset($item['videoShare']);
            unset($item['didILike']);
            unset($item['user']);
            return $item;
        });
        $publishedVideoArray = $myPublishedVideos->toArray();
        $friendsVideos = Video::with(['videoShare.shareWith','user','comments','didILike','saved_videos.video'])
            ->whereIn('user_id',$myFriends->pluck('id'))->where(['is_published'=>1])->get();
        $friendsVideos = $friendsVideos->map(function($item){
            if($item['saved_videos'] != null && $item['saved_videos']['video'] != null){
                $item['is_favourite'] = true;
            }else{
                $item['is_favourite'] = false;
            }
            unset($item['saved_videos']);
            if($item->videoShare != null){
                $item['shared_with'] = $item->videoShare->shareWith->toArray();
            }else{
                $item['shared_with'] = null;
            }
            $item['who_shared'] = $item->user->toArray();
            $item['did_i_like'] = ($item->didILike != null)?true:false;
            unset($item['videoShare']);
            unset($item['didILike']);
            unset($item['user']);
            return $item;
        });
        foreach($friendsVideos as $k => $video){
            $publishedVideoArray[] = $video->toArray();
        }
        return response()->json(['errors'=>null,'message'=>'Feeds collected successfully!','feeds'=>$publishedVideoArray]);
    }
}
