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
    Route::post('/cf/{id}', 'OjController@cfOverall');
    Route::post('/uva/{id}', 'OjController@uvaOverall');
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
    Route::post('/stats/{id}', 'PresetController@stats');
});






