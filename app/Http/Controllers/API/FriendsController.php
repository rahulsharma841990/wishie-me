<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\Friend;
use App\Http\Controllers\Controller;
use App\LabelMapping;
use App\User;
use Carbon\Carbon;
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
        $message = $fromUser->first_name.' '.$fromUser->last_name.' sent you a friend request';
        Friend::sendNotification($toUser,$fromUser,$message);
        return response()->json(['errors'=>null,'message'=>'Friend request sent successfully!']);
    }

    public function acceptRejectFriendRequest(Request $request){
        $toUser = Auth::user();
        $friendModel = Friend::where(['friend_id'=>$toUser->id,'user_id'=>$request->from_user])->first();
        if($request->accept_reject == 1){
            $friendModel->is_accepted = 1;
            $saveToMyFriend = new Friend;
            $saveToMyFriend->user_id = $toUser->id;
            $saveToMyFriend->friend_id = $request->from_user;
            $saveToMyFriend->is_accepted = 1;
            $saveToMyFriend->save();
            $friendModel->save();
            $fromUser = User::find($request->from_user);
            $message = $toUser->first_name.' '.$toUser->last_name.' accepted your friend request';
            $birthdayModel = new Birthday;
            $birthdayModel->first_name = $toUser->first_name;
            $birthdayModel->last_name = $toUser->last_name;
            $birthdayModel->friend_id = $toUser->id;
            $birthdayModel->birthday = Carbon::parse($toUser->dob)->format('Y-m-d');
            $birthdayModel->created_by = $request->from_user;
            $birthdayModel->save();
            $labelMapping = new LabelMapping;
            $labelMapping->birthday_id = $birthdayModel->id;
            $labelMapping->label_id = 3;
            $labelMapping->user_id = $request->from_user;
            $labelMapping->save();

            $birthdayModel = new Birthday;
            $birthdayModel->first_name = $fromUser->first_name;
            $birthdayModel->last_name = $fromUser->last_name;
            $birthdayModel->friend_id = $fromUser->id;
            $birthdayModel->birthday = Carbon::parse($fromUser->dob)->format('Y-m-d');
            $birthdayModel->created_by = $toUser->id;
            $birthdayModel->save();
            $labelMapping = new LabelMapping;
            $labelMapping->birthday_id = $birthdayModel->id;
            $labelMapping->label_id = 3;
            $labelMapping->user_id = $toUser->id;
            $labelMapping->save();
            Friend::sendNotification($fromUser,$toUser,$message);
            return response()->json(['errors'=>null,'message'=>'Friend request accepted successfully!']);
        }else{
            $friendModel->is_rejected = 1;
            $friendModel->save();
            return response()->json(['errors'=>null,'message'=>'Friend request rejected!']);
        }
    }

    public function listOfFriendRequests(){
        $user = Auth::user();
        $sentByMe = Friend::with(['friend'])->where(['user_id'=>$user->id])->whereNull('is_accepted')->get();
        $sentByMe = $sentByMe->map(function($query){
            return $query->friend;
        });
        $sendToMe = Friend::with(['user'])->where(['friend_id'=>$user->id])->whereNull('is_accepted')->get();
        $sendToMe = $sendToMe->map(function($query){
            return $query->user;
        });
        return response()->json(['errors'=>null,'message'=>'Friends collected successfully!','send_by_me'=>$sentByMe->toArray(),
            'sent_to_me'=>$sendToMe->toArray()]);
    }

    public function cancelFriendRequest(Request $request){
        $user = Auth::user();
        $friendModel = Friend::where(['id'=>$request->request_id,'user_id'=>$user->id])->first();
        $friendModel->delete();
        return response()->json(['errors'=>null,'message'=>'Friend request canceled successfully!']);
    }

    public function friendsList($userId = null){
        if($userId == null){
            $user = Auth::user();
        }else{
            $user = User::find($userId);
        }
        $usersArray = [];
        $friends = Friend::with(['user'])->where(['friend_id'=>$user->id])->get()->toArray();
        foreach($friends as $key => $user){
            $usersArray[$key] = $user['user'];
            if($user['is_accepted'] == 1){
                $usersArray[$key]['is_my_friend'] = true;
                $usersArray[$key]['is_friend_request_sent'] = false;
            }else{
                $usersArray[$key]['is_my_friend'] = false;
                $usersArray[$key]['is_friend_request_sent'] = true;
            }
        }
        return response()->json(['errors'=>null,'message'=>'Friends collected successfully!','users'=>$usersArray]);
    }
}
