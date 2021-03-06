<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/deploy','DeploymentController@deploy');

Route::get('/click/{ad_uuid}/{ch_uuid}/to','ClickV1Controller@to');
Route::get('/active','ActiveController@index');