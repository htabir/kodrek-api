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

Route::group([
    'middleware'=>  'api',
    'namespace' =>  'App\Http\Controllers',
    'prefix'    =>  'auth'
], function ($router){
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('change/password', 'AuthController@changePassword');
    Route::post('change/dailyGoal', 'AuthController@changedailyGoal');
    Route::post('change/presetDailyGoal', 'AuthController@changePresetGoal');
    //checking
    Route::post('check/email', 'AuthController@checkEmail');
    Route::post('check/username', 'AuthController@checkUsername');
});


Route::group([
    'middleware'=>  'api',
    'namespace' =>  'App\Http\Controllers',
    'prefix'    =>  'oj'
], function ($router){
    Route::post('/addCf/{id}', 'OjController@addCf');
    Route::post('/addUva/{id}', 'OjController@addUva');

    Route::post('/cf/stats/{id}', 'OjController@cfStats');
    Route::post('/cf/solved/{id}', 'OjController@cfSolved');
    Route::post('/cf/unsolved/{id}', 'OjController@cfUnsolved');

    Route::post('/uva/stats/{id}', 'OjController@uvaStats');
    Route::post('/uva/solved/{id}', 'OjController@uvaSolved');
    Route::post('/uva/unsolved/{id}', 'OjController@uvaUnsolved');
    Route::get('/uva/refresh', 'OjController@refreshUva');
    
    //checking
    Route::post('/check/cf', 'OjController@checkCf');
    Route::post('/check/uva', 'OjController@checkUva');
});

Route::group([
    'middleware'=>  'api',
    'namespace' =>  'App\Http\Controllers',
    'prefix'    =>  'preset'
], function ($router){
    Route::post('/create', 'PresetController@create');
    Route::post('/set/{presetId}', 'PresetController@setPreset');
    Route::post('/stats/{username}', 'PresetController@stats');
    Route::post('/list', 'PresetController@presetList');
    Route::post('/details/{id}', 'PresetController@presetDetails');
    Route::post('/like', 'PresetController@like');
});






