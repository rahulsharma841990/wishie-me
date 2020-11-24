<?php

namespace App\Http\Controllers\API;

use App\Friend;
use App\Http\Controllers\Controller;
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


    }
}
