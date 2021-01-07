<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {

    Route::post('pay/wechatNotify', 'PayController@wechatNotify');
    Route::get('pay/getOrder', 'PayController@getOrder');

    Route::group(['middleware' => ['checkPayToken']], function () {

        Route::post('pay/unifiedOrder', 'PayController@unifiedOrder');
        Route::post('payment/getWXPayQRCode', 'PayController@getWXPayQRCode');

    });
});
