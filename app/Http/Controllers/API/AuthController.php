<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\RegisterRequest;
use App\Label;
use App\Reminder;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $requestData = $request->except(['password','profile_image']);
        $requestData['password'] = Hash::make($request->password);
        if($request->has('profile_image') && $request->profile_image != null){
            $imageName = $this->uploadFile($request);
            $requestData['profile_image'] = $imageName;
        }
        $user = User::create($requestData);
        $this->createReminder($user);
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['errors'=>null,'message'=>'user created successfully!','user'=>$user,'access_token'=>$accessToken]);
    }
    //UsernameValidateRequest
    public function validateUsername(Request $request){
        $userModel = User::where(['username'=>$request->username])->first();
        if($userModel != null){
            return response(['errors'=>['username'=>['The username has already been taken.']],'message'=>'Username not available!','username'=>$request->username],422);
        }else{
            return response(['errors'=>null,'message'=>'Username available!','username'=>$request->username]);
        }
    }

    private function uploadFile($request){
        $image = $request->input('profile_image');
        preg_match("/data:image\/(.*?);/",$image,$image_extension);
        $image = preg_replace('/data:image\/(.*?);base64,/','',$image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'image_' . time() . '.' . $image_extension[1];
        Storage::disk('profile_images')->put($imageName,base64_decode($image));
        return $imageName;
    }

    private function uploadHeaderFile($request){
        $image = $request->input('header_image');
        preg_match("/data:image\/(.*?);/",$image,$image_extension);
        $image = preg_replace('/data:image\/(.*?);base64,/','',$image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'image_' . time() . '.' . $image_extension[1];
        Storage::disk('profile_images')->put($imageName,base64_decode($image));
        return $imageName;
    }

    public function login(LoginRequest $request){
        $where = $this->credentials($request);
        $user = User::Where($where)->first();
        if($user == null){
            return response(['errors'=>['username'=>['Wrong user details!']]],422);
        }else{
            if(Hash::check($request->password,$user->password)){
                return response()->json(['errors'=>null,'message'=>'User logged in successfully!','user'=>$user,'access_token'=>$user->createToken('authToken')->accessToken]);
            }else{
                return response()->json(['errors'=>['username'=>['Wrong user details!']]],422);
            }
        }
    }

    protected function credentials($request){
        if(is_numeric($request->username)){
            return ['phone'=>$request->username];
        }
        elseif (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            return ['email' => $request->username];
        }
        return ['username' => $request->username];
    }

    public function socialRegister(Request $request){
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'username' => 'required|unique:users',
            'dob' => 'required'
        ];
        if($request->has('phone') && $request->phone != null){
            $rules['phone'] = 'unique:users';
        }
        $this->validate($request,$rules);
        $socialRequest = $request->only(['facebook_id','gmail_id','twitter_id','apple_id']);
        $isSocialDetailsExistUserModel = User::where(collect($socialRequest)->filter()->toArray())->first();
        if($isSocialDetailsExistUserModel != null){
            return response()->json(['errors'=>null,'message'=>'User logged in successfully!',
                'access_token'=>$isSocialDetailsExistUserModel->createToken('authToken')->accessToken,'user'=>$isSocialDetailsExistUserModel]);
        }
        if($request->has('email') && $request->email != null){
            $userModel = User::where(['email'=>$request->email])->first();
            if($userModel != null){
                $requestData = $request->only(['facebook_id','gmail_id','twitter_id','apple_id']);
                $requestData = array_filter($requestData, function($item){
                    return $item != null;
                });
                $userModel->fill($requestData)->save();
                $this->createReminder($userModel);
                return response()->json(['errors'=>null,'message'=>'User logged in successfully!',
                    'user'=>$userModel->toArray(),'access_token'=>$userModel->createToken('authToken')->accessToken]);
            }else{
                $requestData = $request->except(['password','profile_image']);
                if($request->has('profile_image') && $request->profile_image != null){
                    $imageName = $this->uploadFile($request);
                    $requestData['profile_image'] = $imageName;
                }
                $requestData['password'] = Hash::make($request->password);
                $user = User::create($requestData);
                $this->createReminder($user);
                return response()->json(['errors'=>null,'message'=>'user created successfully!',
                    'user'=>$user->toArray(),'access_token'=>$user->createToken('authToken')->accessToken]);
            }
        }else{
            $requestData = $request->except(['password','profile_image']);
            if($request->has('profile_image') && $request->profile_image != null){
                $imageName = $this->uploadFile($request);
                $requestData['profile_image'] = $imageName;
            }
            $requestData['password'] = Hash::make($request->password);
            $user = User::create($requestData)->toArray();
            $this->createReminder($user);
            return response()->json(['errors'=>null,'message'=>'user created successfully!',
                'user'=>$user,'access_token'=>$user->createToken('authToken')->accessToken]);
        }
    }

    public function removeUser(Request $request){
        User::find(\Auth::user()->id);
        return response()->json(['errors'=>null,'message'=>'User removed successfully!']);
    }

    public function removeAllUsers(Request $request){
        if($request->has('security_token') && $request->security_token == 'vOMjm0e0qtIsLv2524wvEvPRD9OC7maZ'){
            User::where('id','>',1)->delete();
            return response()->json(['errors'=>null,'message'=>'Users deleted successfully!']);
        }else{
            return response()->json(['errors'=>['token'=>['Unable to verify the token']],'message'=>'Something went wrong!'],422);
        }
    }

    public function socialLogin(Request $request){
        if($request->has('email')){
            $userModel = User::where(['email'=>$request->email])->first();
            $requestData = $request->except(['email']);
            $requestData = array_filter($requestData, function($item){
                return $item != null;
            });
            if($userModel == null){
                return response()->json(['errors'=>['email'=>['Use account not found with give details!']],'message'=>'Use account not found with give details!'],422);
            }else{
                $userModel->fill($requestData);
                $userModel->save();
                return response()->json(['errors'=>null,'message'=>'User login successfully!','user'=>$userModel,'access_token'=>$userModel->createToken('authToken')->accessToken]);
            }
        }else{
            return response()->json(['errors'=>['email'=>['Email id field is required!']],'message'=>'Email id is missing!'],422);
        }
    }

    public function getUserProfile(){
        $userDetails = Auth::user();
        return response()->json(['errors'=>null,'user_details'=>$userDetails->toArray()]);
    }

    public function updateProfile(ProfileUpdateRequest $request){
        $userModel = Auth::user();
        $userModel->fill($request->except(['profile_image','header_image','password']));
        if($request->has('profile_image') && $request->profile_image != null){
            $imageName = $this->uploadFile($request);
            $userModel->profile_image = $imageName;
        }
        if($request->has('header_image') && $request->header_image != null){
            $imageName = $this->uploadHeaderFile($request);
            $userModel->header_image = $imageName;
        }
        if($request->has('password')){
            $userModel->password = Hash::make($request->password);
        }
        $userModel->save();
        return response()->json(['errors'=>null,'message'=>'Profile updated successfully!','user'=>$userModel]);
    }

    protected function createReminder($user){
        $adminLabels = Label::whereCreatedBy(0)->get();
        foreach($adminLabels as $k => $label){
            $reminderModel = new Reminder;
            $reminderModel->label_id = $label->id;
            $reminderModel->title = 'Day of Occasion';
            $reminderModel->time = '10:00 AM';
            $reminderModel->user_id = $user->id;
            $reminderModel->save();
        }
    }

    public function refreshToken(Request $request){
        $rules = [
            'device_token' => 'required'
        ];
        $request->validate($rules);
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();
        return response()->json(['errors'=>null,'message'=>'Device token updated successfully!']);
    }

    public function searchUser($username = ''){
        $user = Auth::use();
        $users = User::with(['friends'=>function($query){
            $query->with('user');
        }])->where('username','like','%'.$username.'%')
            ->orWhere('first_name','like','%'.$username.'%')
            ->where('id','!=',$user->id)
            ->get()->toArray();
        return response()->json(['errors'=>null,'message'=>'Users collected successfully!','users'=>$users]);
    }

}
