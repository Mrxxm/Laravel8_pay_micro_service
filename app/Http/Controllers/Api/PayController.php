<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Response;
use App\Services\Impl\AppIdServiceImpl;
use App\Services\Impl\PayServiceImpl;
use App\Utils\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayController
{

    public function unifiedOrder(Request $request)
    {
        $data = $request->only('serve_type', 'pay_type', 'body', 'order_no', 'total_price', 'openid', 'user_id', 'return_url');

        $validator = Validator::make($data, [
            'user_id'          => 'required|string',
            'serve_type'       => 'required|string',
            'pay_type'         => 'required|string',
            'body'             => 'required|string',
            'order_no'         => 'required|string',
            'total_price'      => 'required|string',
            'return_url'       => 'required|string',
            'openid'           => 'string',
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse([], $validator->errors()->first());
        }

        $service = new PayServiceImpl();

        try {
            $result = $service->unifiedOrder($data);
        } catch (\Exception $exception) {
            return Response::errorResponse([], $exception->getMessage());
        }

        return Response::successResponse($result);
    }

    /**
     * 支付回调接口
     * 每隔一段时间调用
     * 频率：15/15/30/180/1800/1800/1800/1800/3600, 单位:秒
     */
    public function wechatNotify()
    {
        $xml     = file_get_contents("php://input");
        $jsonXml = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $notifyData = json_decode($jsonXml, true); // 转成数组

        Log::channel('notify')->debug($notifyData);
        // 测试数据
//        $notifyData['out_trade_no'] = '2101086KBPWX4568';
//        $notifyData['trade_type'] = 'NATIVE';
//        $notifyData['transaction_id'] = '123456789123456789';

        $appIdService = new AppIdServiceImpl();
        $appIds = $appIdService->model->select('id as app_id')->get();
        if (count($appIds)) {
            $appIds = resultToArray($appIds);
            foreach ($appIds as $appId) {
                $key     = "order_" . $appId['app_id'];
                $hashKey = $notifyData['out_trade_no'];
                $hashValue = (Redis::getInstance())->hGet($key, $hashKey);
                if ($hashValue) {
                    $hashValue = json_decode($hashValue, true);
                    $hashValue['pay_status'] = 20;
                    $hashValue['trade_type'] = $notifyData['trade_type'];
                    $hashValue['transaction_id'] = $notifyData['transaction_id'];

                    // 更新状态
                    (Redis::getInstance())->hSet($key, $hashKey, json_encode($hashValue));
                    // 通知第三方订单接口
                    if ($key == 'order_1') {
                        try {
                            api($hashValue['return_url'], "merchantOrderId={$hashKey}",'POST');
                        } catch (\Exception $e) {
                            Log::channel('notify_to_app')->debug($e->getMessage());
                        }
                    }

                    break;
                }
            }
        }

        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    public function getOrder(Request $request)
    {
        $data = $request->only('app_id', 'order_no');

        $validator = Validator::make($data, [
            'app_id'           => 'required|integer',
            'order_no'         => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse([], $validator->errors()->first());
        }

        $service = new PayServiceImpl();

        try {
            $result = $service->getOrder($data);
        } catch (\Exception $exception) {
            return Response::errorResponse([], $exception->getMessage());
        }

        return Response::successResponse($result);
    }

    public function getWXPayQRCode(Request $request)
    {
        $data = $request->only('app_id', 'order_no');

        $validator = Validator::make($data, [
            'app_id'           => 'required|integer',
            'order_no'         => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse([], $validator->errors()->first());
        }

        $service = new PayServiceImpl();

        try {
            $result = $service->getQRCode($data);
        } catch (\Exception $exception) {
            return Response::errorResponse([], $exception->getMessage());
        }

        return Response::successResponse($result);
    }
}
