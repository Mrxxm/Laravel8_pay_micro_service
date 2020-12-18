<?php


namespace App\Http\Middleware;


use App\Http\Controllers\Response;
use App\Services\Impl\AppIdServiceImpl;
use App\Utils\Redis;
use Closure;
use Illuminate\Support\Facades\Validator;

class CheckPayToken
{
    function handle($request, Closure $next, $guard = null)
    {
        $data = request()->only('app_id', 'token', 'time');

        $validator = Validator::make($data, [
            'app_id'           => 'required|integer',
            'token'            => 'required|string',
            'time'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::missParamResponse([], $validator->errors()->first());
        }

        $appIdService = new AppIdServiceImpl();
        $app = $appIdService->model->find($data['app_id']);
        if (!$app) {
            return Response::errorResponse([], '不存在该appId,请联系支付平台负责人申请开通');
        }

        // 时间检测
        if ($app->app_expire + $data['time'] < time()) {
            return Response::errorResponse([], '请求token时间已过期，请重新生成token');
        }

        // token检测
        $tokenFields = [$data['time'], $data['app_id'], $app->app_key];
        if ($data['token'] != md5(implode($app['stitching_symbol'], $tokenFields))) {
            return Response::errorResponse([], '不合法的请求，请检验token是否合法');
        }

        // redis token校验
        $redisClient = Redis::getInstance();
        $isExist = $redisClient->hGet('pay_token', $data['app_id']);
        if ($isExist) {
            $appRedis = json_decode($isExist, true);
            if ($appRedis['token'] == $data['token']) {
                return Response::errorResponse([], '重复请求，token已使用');
            }
        }
        $appRedis = [
            'token' => $data['token']
        ];
        $redisClient->hSet('pay_token', $data['app_id'], json_encode($appRedis));

        return $next($request);
    }
}
