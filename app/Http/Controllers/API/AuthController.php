<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SocialLoginRequest;
use App\Http\Requests\UsernameValidateRequest;
use App\User;
use Illuminate\Http\Request;
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
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['errors'=>null,'message'=>'user created successfully!','user'=>$user,'access_token'=>$accessToken]);
    }
    //UsernameValidateRequest
    public function validateUsername(Request $request){
        $userModel = User::where(['username'=>$request->username])->first();
        if($userModel != null){
            return response(['errors'=>['username'=>['The username has already been taken.']],'message'=>'Username not available!','username'=>$request->username]);
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

    public function login(LoginRequest $request){
        $where = $this->credentials($request);
        $user = User::Where($where)->first();
        if($user == null){
            return response(['errors'=>['username'=>['Wrong user details!']]]);
        }else{
            if(Hash::check($request->password,$user->password)){
                return response()->json(['errors'=>null,'message'=>'User logged in successfully!','user'=>$user,'access_token'=>$user->createToken('authToken')->accessToken]);
            }else{
                return response(['errors'=>['username'=>['Wrong user details!']]]);
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

    public function socialLogin(Request $request){
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
            'gender' => 'required',
            'username' => 'required|unique:users',
            'dob' => 'required'
        ];
        if($request->has('phone') && $request->phone != null){
            $rules['phone'] = 'unique:users';
        }
        $this->validate($request,$rules);
        if($request->has('email') && $request->email != null){
            $userModel = User::where(['email'=>$request->email])->first();
            if($userModel != null){
                $requestData = $request->only(['facebook_id','gmail_id','twitter_id','apple_id']);
                $userModel->fill($requestData)->save();
            }
            if($userModel != null){
                return response()->json(['errors'=>null,'message'=>'User logged in successfully!',
                    'user'=>$userModel,'access_token'=>$userModel->createToken('authToken')->accessToken]);
            }else{
                $requestData = $request->except(['password','profile_image']);
                $requestData['password'] = Hash::make($request->password);
                $user = User::create($requestData);
                return response()->json(['errors'=>null,'message'=>'user created successfully!',
                    'user'=>$user,'access_token'=>$user->createToken('authToken')->accessToken]);
            }
        }else{
            $requestData = $request->except(['password','profile_image']);
            $requestData['password'] = Hash::make($request->password);
            $user = User::create($requestData);
            return response()->json(['errors'=>null,'message'=>'user created successfully!',
                'user'=>$user,'access_token'=>$user->createToken('authToken')->accessToken]);
        }
    }

    public function removeUser(Request $request){
        User::find(\Auth::user()->id);
        return response()->json(['errors'=>null,'message'=>'User removed successfully!']);
    }
}
