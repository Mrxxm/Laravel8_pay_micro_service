<?php


namespace App\Utils;


class ClassArr
{
    public static function importClassStat()
    {
        return [
            // pay
            // ocr
            "ali_ocr"            => "App\Utils\OCR\AliOcr",
            // sms
            "ali_sms"            => "App\Utils\SMS\AliSms",
            "mw_sms"             => "App\Utils\SMS\MWSms",
            // oss
            "ali_oss"            => "App\Utils\OSS\AliOss",
            "ali_oss_new"        => "App\Utils\OSS\AliOssNew",
            "qi_niu_oss"         => "App\Utils\OSS\QiNiuOss",
        ];
    }

    public static function initClass($type, $classs, $params = [], $needInstance = false)
    {
        if(!array_key_exists($type, $classs)) {
            throw new \Exception("类型：{$type} 的类库找不到");
        }
        $className = $classs[$type];

        return $needInstance == true ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;

    }
}
