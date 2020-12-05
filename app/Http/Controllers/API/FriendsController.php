<?php

namespace App\Http\Controllers\API;

use App\Friend;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendsController extends Controller
{
    public function sendFriendRequest(Request $request){
        $fromUser = Auth::user()->id;
        $friendModel = Friend::firstOrNew(['user_id'=>$fromUser,'friend_id'=>$request->to_user]);
        $friendModel->user_id = $fromUser;
        $friendModel->friend_id = $request->to_user;
        $friendModel->save();
        $toUser = User::find($request->to_user);
        $message = $toUser->first_name.' '.$toUser->last_name.' sent you a friend request';
        Friend::sendNotification($toUser,$fromUser,$message);
        return response()->json(['errors'=>null,'message'=>'Friend request sent successfully!']);
    }

    public function acceptRejectFriendRequest(Request $request){
        $toUser = Auth::user()->id;
        $friendModel = Friend::where(['friend_id'=>$toUser,'user_id'=>$request->from_user])->first();
        if($request->accept_reject == 1){
            $friendModel->is_accepted = 1;
            $saveToMyFriend = new Friend;
            $saveToMyFriend->user_id = $toUser;
            $saveToMyFriend->friend_id = $request->from_user;
            $saveToMyFriend->is_accepted = 1;
            $saveToMyFriend->save();
            $friendModel->save();
            $fromUser = User::find($request->from_user);
            $message = $fromUser->first_name.' '.$fromUser->last_name.' accepted your friend request';
            Friend::sendNotification($fromUser,$toUser,$message);
            return response()->json(['errors'=>null,'message'=>'Friend request accepted successfully!']);
        }else{
            $friendModel->is_rejected = 1;
            return response()->json(['errors'=>null,'message'=>'Friend request rejected!']);
        }
    }

    public function listOfFriendRequests(){
        $user = Auth::user();
        $sentByMe = Friend::with(['friend'])->where(['user_id',$user->id])->whereNull('is_accepted')->get();
        $sendToMe = Friend::with(['friend'])->where(['friend_id'=>$user->id])->whereNull('is_accepted')->get();
        return response()->json(['errors'=>null,'message'=>'Friends collected successfully!','send_by_me'=>$sentByMe->toArray(),
            'sent_to_me'=>$sendToMe->toArray()]);
    }

    public function cancelFriendRequest(Request $request){
        $user = Auth::user();
        $friendModel = Friend::where(['id'=>$request->request_id,'user_id'=>$user->id])->first();
        $friendModel->delete();
        return response()->json(['errors'=>null,'message'=>'Friend request canceled successfully!']);
    }
}
