<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {

    Route::group(['middleware' => ['checkPayToken']], function () {

        Route::any('pay/unifiedOrder', 'PayController@unifiedOrder');

    });
});
