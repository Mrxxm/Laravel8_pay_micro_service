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
        $this->wConfig = [
            'miniapp_id' => env('WECHAT_MINIAPP_ID'),
            'mch_id'     => env("WECHAT_MCH_ID"),
            'key'        => env("WECHAT_KEY"),
            'notify_url' => "https://pay.kenrou.cn/api/pay/wechatNotify",
        ];
        $this->aConfig = [];
    }

    public function unifiedOrder(array $data): array
    {
        $serveType  = $data['serve_type'];
        $payType    = $data['pay_type'];
        try {
            $result = $this->serveAndPay($serveType, $payType);
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

    private function serveAndPay(string $serveType, string $payType)
    {
        $switchType = $serveType . '_' . $payType;

        switch ($switchType) {
            case 'wechat_pay_mini':
                $result = Pay::wechat()->miniapp();
                break;
            case 'ali_pay_mini':
                $result = Pay::alipay()->mini($this->aConfig);
                break;
            default:
                throw new \Exception('未匹配到服务商和支付类型，请重试');
                break;
        }

        $payReturn = resultToArray($result);
        Log::channel('pay')->debug($payReturn);
        return $result;
    }
}
