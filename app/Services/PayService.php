<?php


namespace App\Services;


interface PayService
{
    // 小程序支付
    public function unifiedOrder(array $data);
}
