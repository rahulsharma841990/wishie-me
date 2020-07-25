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

Route::get('remove/users','API\AuthController@removeAllUsers');

Route::group(['middleware'=>'cors'], function(){
    Route::post('register','API\AuthController@register');
    Route::post('login','API\AuthController@login');

    Route::post('social/auth','API\AuthController@socialRegister');
    Route::post('social/login','API\AuthController@socialLogin');

    Route::post('validate/username','API\AuthController@validateUsername');

    Route::group(['middleware'=>'auth:api'], function(){
        Route::get('remove/user','API\AuthController@removeUser');

        //Labels
        Route::post('labels','API\LabelsController@create');
        Route::get('labels','API\LabelsController@getLabels');
        Route::delete('labels/{id}','API\LabelsController@destroy');
        Route::put('labels/{id}','API\LabelsController@update');
        Route::get('label/counts','API\LabelsController@labelCounts');

        //Birthday
        Route::post('birthday','API\BirthdayController@create');
        Route::get('dashboard','API\BirthdayController@getBirthdays');
        Route::put('birthday/update/{id}','API\BirthdayController@edit');
        Route::delete('birthday/delete/{id}','API\BirthdayController@delete');
    });
});
Route::group(['middleware' => ['web']], function() {
    Route::get('image/{disk}/{image}',['as'=>'public-image','uses'=>'API\ImageController@image']);
});
