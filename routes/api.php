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
        Route::get('label/empty/{id}','API\LabelsController@makeLabelEmpty');
        Route::post('birthday/move','API\LabelsController@moveBirthdays');

        //Birthday
        Route::post('birthday','API\BirthdayController@create');
        Route::get('dashboard','API\BirthdayController@getBirthdays');
        Route::put('birthday/update/{id}','API\BirthdayController@edit');
        Route::delete('birthday/delete/{id}','API\BirthdayController@delete');
        Route::get('birthdays','API\BirthdayController@birthdaysList');

        //User Profile
        Route::get('profile','API\AuthController@getUserProfile');
        Route::put('profile','API\AuthController@updateProfile');

        //Reminder
        Route::post('reminder','API\RemindersController@saveReminder');
        Route::get('reminders','API\RemindersController@getReminders');
        Route::put('reminder/{id}','API\RemindersController@updateReminder');
        Route::delete('reminder/{id}','API\RemindersController@deleteReminder');


        //Birthday Reminders
        Route::get('birthday/reminders/{birthday_id}','API\BirthdayReminderController@getBirthdayReminders');
        Route::post('birthday/reminder/{birthday_id}','API\BirthdayReminderController@create');
        Route::put('set/reminder/{birthday_reminder_id}','API\BirthdayReminderController@enableDisableReminder');
        Route::delete('birthday/reminder/{birthday_reminder_id}','API\BirthdayReminderController@deleteBirthdayReminder');

        //Refresh Token
        Route::post('refresh/token','API\AuthController@refreshToken');

        //Notification
        Route::post('notification/send','API\NotificationController@sendNotification');

    });
});
Route::group(['middleware' => ['web']], function() {
    Route::get('image/{disk}/{image}',['as'=>'public-image','uses'=>'API\ImageController@image']);
    Route::get('tone/{disk}/{file}',['as'=>'public-tone','uses'=>'API\ToneController@tone']);
});
