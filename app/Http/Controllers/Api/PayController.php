<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayController
{
    public function unifiedOrder(Request $request)
    {
        return Response::successResponse();
        $data = $request->only('keyword');

        $validator = Validator::make($data, [
            'keyword'             => 'string',
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse([], $validator->errors()->first());
        }

        $service = new AppIdServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::errorResponse([], $exception->getMessage());
        }

        return Response::successResponse($result);
    }
}
