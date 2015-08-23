<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'IndexController@index');
Route::get('/getUserId/{userName}', 'IndexController@getUserId');
Route::get('/getUserInfo/{userName}', 'IndexController@getUserInfo');
Route::get('/getUserPhotos/{userName}/{next_max_id?}', 'IndexController@getUserPhotos');
Route::get('/getPhotoDetails/{photoId}/{likes?}', 'IndexController@getPhotoDetails');
Route::get('/getPhotoDetails/{photoId}/{comments?}', 'IndexController@getPhotoDetails');

