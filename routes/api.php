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
});


Route::group([
    'middleware'=>  'api',
    'namespace' =>  'App\Http\Controllers',
    'prefix'    =>  'oj'
], function ($router){
    Route::post('/addCf/{id}', 'OjController@addCf');
    Route::post('/addUva/{id}', 'OjController@addUva');
    Route::post('/cf', 'OjController@cfOverall');
    Route::post('/uva', 'OjController@uvaOverall');
});






