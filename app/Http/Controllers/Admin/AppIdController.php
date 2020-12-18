<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Response;
use App\Services\Impl\AppIdServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppIdController
{
    public function list(Request $request)
    {
        $data = $request->only('keyword');

        $validator = Validator::make($data, [
            'keyword'             => 'string',
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse();
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
