<?php


namespace App\Services;


interface PayService
{
    // 小程序支付
    public function miniUnifiedOrder(array $data) : array;
}
