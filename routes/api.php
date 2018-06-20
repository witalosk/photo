<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// 認証は面倒なので一旦省略
Route::resource('/photos', 'PhotoController', ['except' => ['create', 'edit']]);
Route::get('/users/{screen_name}', 'UserController@userInfo');
