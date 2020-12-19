<?php


namespace App\Services\Impl;


use App\Services\PayService;
use App\Utils\Redis;
use Illuminate\Support\Facades\Log;
use Yansongda\LaravelPay\Facades\Pay;

class PayServiceImpl implements PayService
{
    private $wConfig = null;
    private $aConfig = null;

    public function __construct()
    {
    }

    public function unifiedOrder(array $data) : array
    {
        $serveType  = $data['serve_type'];
        $payType    = $data['pay_type'];

        try {
            $result = $this->serveAndPayToPay($serveType, $payType, $data);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        $orderData = [
            'pay_status'  => 0, // 待支付 1: 已支付
            'pay_type'    => $payType,
            'serve_type'  => $serveType,
            'create_time' => time()
        ];

        $key     = "order_" . request('app_id');
        $hashKey = $data['order_no'];
        $value   = json_encode($orderData);
        try {
            (Redis::getInstance())->hSet($key, $hashKey, $value);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $result;
    }

    private function serveAndPayToPay(string $serveType, string $payType, array $data)
    {
        $switchType = $serveType . '_pay_' . $payType;
        $fields = [
            'out_trade_no' => $data['order_no'],
            'total_fee'    => $data['total_price'] * 100,
            'body'         => $data['body'],
            'openid'       => $data['openid'],
            'notify_url'   => 'https://pay.kenrou.cn/api/pay/wechatNotify'
        ];

        switch ($switchType) {
            case 'wechat_pay_mini':
                $result = Pay::wechat()->miniapp($fields);
                break;
            case 'ali_pay_mini':
                $result = Pay::alipay()->mini();
                break;
            default:
                throw new \Exception('未匹配到服务商和支付类型，请重试');
                break;
        }

        $payReturn = resultToArray($result);
        Log::channel('pay')->debug($payReturn);

        return $payReturn;
    }

    public function getOrder(array $data): array
    {
        $key     = "order_" . $data['app_id'];
        $hashKey = $data['order_no'];
        try {
            $redisOrder = (Redis::getInstance())->hGet($key, $hashKey);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
        if ($redisOrder) {
            return json_decode($redisOrder, true);
        }

        return [];
    }
}
