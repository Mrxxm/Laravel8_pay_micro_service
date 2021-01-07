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
        $data = $request->only('serve_type', 'pay_type', 'body', 'order_no', 'total_price', 'openid', 'user_id');

        $validator = Validator::make($data, [
            'user_id'          => 'required|string',
            'serve_type'       => 'required|string',
            'pay_type'         => 'required|string',
            'body'             => 'required|string',
            'order_no'         => 'required|string',
            'total_price'      => 'required|string',
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

        $appIdService = new AppIdServiceImpl();
        $appIds = $appIdService->model->selelct('id as app_id')->get();
        if (count($appIds)) {
            $appIds = resultToArray($appIds);
            foreach ($appIds as $appId) {
                $key     = "order_" . $appId['app_id'];
                $hashKey = $notifyData['order_no'];
                $hashValue = (Redis::getInstance())->hGet($key, $hashKey);
                if ($hashValue) {
                    $hashValue = json_decode($hashValue, true);
                    $hashValue['pay_status'] = 20;
                    (Redis::getInstance())->hSet($key, $hashKey, json_encode($hashValue));
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
