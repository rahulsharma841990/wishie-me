<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware'=>'cors'], function(){
    Route::post('register','API\AuthController@register');
    Route::post('login','API\AuthController@login');

    Route::post('social/auth','API\AuthController@socialLogin');

    Route::post('validate/username','API\AuthController@validateUsername');

    Route::group(['middleware'=>'auth:api'], function(){

    });
});

