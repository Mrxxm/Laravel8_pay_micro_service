<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Response;
use App\Services\Impl\PayServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayController
{
    public function unifiedOrder(Request $request)
    {
        $data = $request->only('serve_type', 'pay_type', 'body', 'order_no', 'total_price', 'openid');

        $validator = Validator::make($data, [
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
        $notifyData = $jsonXml;

        Log::channel('notify')->debug($notifyData);
    }
}
