<?php


namespace App\Services;


interface PayService
{
    // 支付
    public function unifiedOrder(array $data) : array;
    // 获取订单
    public function getOrder(array $data) : array;
}
