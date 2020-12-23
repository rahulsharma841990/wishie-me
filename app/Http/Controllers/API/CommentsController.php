<?php

namespace App\Http\Controllers\API;

use App\Comment;
use App\Http\Controllers\Controller;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    public function postComment(Request $request){
        if($request->has('video_id') && $request->has('comment')){
            $user = Auth::user();
            $videoModel = Video::find($request->video_id);
            if($videoModel != null){
                $videoCommentModel = Comment::firstOrNew(['video_id'=>$request->video_id,'user_id'=>$user->id]);
                if(!$videoCommentModel->exists){
                    $videoModel->comment_counts = $videoModel->comment_counts + 1;
                    $videoModel->save();
                }
                $videoCommentModel->video_id = $request->video_id;
                $videoCommentModel->user_id = $user->id;
                $videoCommentModel->comment = $request->comment;
                $videoCommentModel->publisher_id = $videoModel->user_id;
                $videoCommentModel->save();
                return response()->json(['errors'=>null,'message'=>'Comment posted successfully!','comment'=>$videoCommentModel]);
            }else{
                return response()->json(['errors'=>['video_id'=>['Video not found!']],
                    'message'=>'Video not found with given video id'],422);
            }
        }else{
            return response()->json(['errors'=>['video_id'=>['Video id is required!'],
                'comment'=>['Comment parameter is required!']],'message'=>'Required fields are missing!'],422);
        }
    }

    public function videoComments($video_id){
        $videoComments = Comment::with(['user'])->where(['video_id'=>$video_id])->get();
        return response()->json(['errors'=>null,'message'=>'Comments collected successfully!','comments'=>$videoComments->toArray()]);
    }

    public function deleteComment($id){
        Comment::find($id)->delete();
        return response()->json(['errors'=>null,'message'=>'Comment deleted successfully!']);
    }
}
