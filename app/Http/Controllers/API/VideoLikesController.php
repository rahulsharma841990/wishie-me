<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Video;
use App\VideoLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoLikesController extends Controller
{
    public function likeVideo(Request $request){
        if($request->has('video_id')){
            $user = Auth::user();
            $videoModel = Video::find($request->video_id);
            if($videoModel == null){
                return response()->json(['errors'=>['video_id'=>['Video not with given id']],
                    'message'=>'Video not found with given id']);
            }else{
                $videoLikeModel = VideoLike::firstOrNew(['video_id'=>$request->video_id,'user_id'=>$user->id]);
                if(!$videoLikeModel->exists){
                    $videoModel->like_counts = $videoModel->like_counts + 1;
                    $videoModel->save();
                }
                $videoLikeModel->video_id = $request->video_id;
                $videoLikeModel->user_id = $user->id;
                $videoLikeModel->publisher_id = $videoModel->user_id;
                $videoLikeModel->save();
                return response()->json(['errors'=>null,'message'=>'Video liked successfully!']);
            }
        }else{
            return response()->json(['errors'=>['video_id'=>['Video id is required!']],'message'=>'Video id is required!']);
        }
    }
}
