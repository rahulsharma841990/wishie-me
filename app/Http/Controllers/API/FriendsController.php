<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\BirthdayReminder;
use App\Friend;
use App\Http\Controllers\Controller;
use App\LabelMapping;
use App\ReportedUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FriendsController extends Controller
{
    public function sendFriendRequest(Request $request){
        $fromUser = Auth::user();
        $friendModel = Friend::firstOrNew(['user_id'=>$fromUser->id,'friend_id'=>$request->to_user]);
        $friendModel->user_id = $fromUser->id;
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
            DB::beginTransaction();
            try{
                $friendModel->is_accepted = 1;
                $saveToMyFriend = new Friend;
                $saveToMyFriend->user_id = $toUser->id;
                $saveToMyFriend->friend_id = $request->from_user;
                $saveToMyFriend->is_accepted = 1;
                $saveToMyFriend->save();
                $friendModel->save();
                $fromUser = User::find($request->from_user);
                $message = $toUser->first_name.' '.$toUser->last_name.' accepted your friend request';
                $baseImageName = null;
                if($toUser->profile_image != null){
                    $baseImageName = basename($toUser->profile_image);
                    Storage::disk('birthday')->put($baseImageName, Storage::disk('profile_images')
                        ->get($baseImageName));
                }

                $birthdayModel = new Birthday;
                $birthdayModel->image = $baseImageName;
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

                $userImageName = null;
                if($fromUser->profile_image != null){
                    $userImageName = basename($fromUser->profile_image);
                    Storage::disk('birthday')->put($userImageName, Storage::disk('profile_images')
                        ->get($userImageName));
                }

                $birthdayModel = new Birthday;
                $birthdayModel->image = $userImageName;
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
                DB::commit();
                return response()->json(['errors'=>null,'message'=>'Friend request accepted successfully!']);
            }catch(\Exception $e){
                DB::rollBack();
                throw $e;
            }
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
        $friendModel = Friend::where(['friend_id'=>$request->request_id,'user_id'=>$user->id])->first();
        if($friendModel != null) {
            $friendModel->delete();
        }
        return response()->json(['errors'=>null,'message'=>'Friend request canceled successfully!']);
    }

    public function friendsList($userId = null){
        if($userId == null){
            $user = Auth::user();
        }else{
            $user = User::find($userId);
        }
        $usersArray = [];
        $friends = Friend::with(['user.videoSharedWithMe.video','friend_of'])->where(['friend_id'=>$user->id])->get()->toArray();
        foreach($friends as $key => $user){
            $usersArray[$key] = $user['user'];
            $videosSharedWithMe = $usersArray[$key]['video_shared_with_me'];
            $usersArray[$key]['video_shared_with_me'] = [];
            if($user['is_accepted'] == 1){
                $usersArray[$key]['is_blocked'] = $user['friend_of']['is_blocked'];
                $usersArray[$key]['is_my_friend'] = true;
                $usersArray[$key]['is_friend_request_sent'] = false;
            }else{
                $usersArray[$key]['is_blocked'] = $user['friend_of']['is_blocked'];
                $usersArray[$key]['is_my_friend'] = false;
                $usersArray[$key]['is_friend_request_sent'] = true;
            }
            foreach($videosSharedWithMe as $k => $videos){
                $usersArray[$key]['video_shared_with_me'][] = $videos['video'];
            }
        }
        return response()->json(['errors'=>null,'message'=>'Friends collected successfully!','users'=>$usersArray]);
    }

    public function blockUnblockUser(Request $request){
        $user = Auth::user();
        if(!in_array($request->block_unblock,[0,1])){
            return response()->json(['errors'=>['block_unblock'=>['Please send the status to 0 or 1 only!']]]);
        }
        Friend::where(['user_id'=>$user->id,'friend_id'=>$request->friend_id])
            ->update(['is_blocked'=>$request->block_unblock]);
        if($request->block_unblock == 1){
            return response()->json(['errors'=>null,'message'=>'Friend blocked successfully!']);
        }else{
            return response()->json(['errors'=>null,'message'=>'Friend un-blocked successfully!']);
        }
    }

    public function blockedUsers(){
        $user = Auth::user();
        $blockedFriends = Friend::with(['user','friend'])->where(['user_id'=>$user->id,'is_blocked'=>1])->get();
        $usersArray = [];
        foreach($blockedFriends as $key => $user){
            $usersArray[$key] = $user['friend']->toArray();
        }
        return response()->json(['errors'=>null,'message'=>'Blocked users collected successfully!','users'=>$usersArray]);
    }

    public function reportUser(Request $request){
        $user = Auth::user();
        $reportedUser = ReportedUser::firstOreNew(['user_id'=>$user->id,'reported_user'=>$request->user_id]);
        $reportedUser->user_id = $user->id;
        $reportedUser->reported_user = $request->user_id;
        $reportedUser->save();

        $isMyFriend = Friend::where(['user_id'=>$user->id,'friend_id'=>$request->user_id])->first();
        if($isMyFriend != null){
            $isMyFriend->is_blocked = 1;
            $isMyFriend->save();
        }
        return response()->json(['error'=>false,'message'=>'User reported successfully!']);
    }

    public function unfriendUser(Request $request){

        $user = Auth::user(); //wo wants to unfriend
        /*
         * Steps:
         * 1) Remove entry from friends table
         * 2) Remove entry from birthday table
         * 3) Remove custom reminder belongs to the birthday
         * */

        DB::beginTransaction();
        try{
            if($request->has('friend_id')){
                Friend::where(['user_id'=>$user->id,'friend_id'=>$request->friend_id])
                    ->orWhere(function($query) use ($request, $user){
                        $query->where(['user_id'=>$request->friend_id,'friend_id'=>$user->id]);
                    })->delete();
                $birthday = Birthday::where(['friend_id'=>$request->friend_id,'created_by'=>$user->id])->first();
                if($birthday != null){
                    BirthdayReminder::where(['birthday_id'=>$birthday->id])->delete();
                    $birthday->delete();
                }
                DB::commit();
                return response()->json(['errors'=>false,'message'=>'User unfriend successfully!']);
            }else{
                return response()->json(['errors'=>['friend'=>['friend id is required!']],'message'=>'Friend id is required!'],422);
            }
        }catch(\Exception $e){
            throw $e;
        }
    }
}
