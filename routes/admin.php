<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'App\Http\Controllers\Admin'], function () {

    // appId管理
    Route::get('/appId/list','AppIdController@list');
    Route::post('/appId/add','AppIdController@add');
    Route::post('/appId/update','AppIdController@update');
    Route::post('/appId/delete','AppIdController@delete');
});
