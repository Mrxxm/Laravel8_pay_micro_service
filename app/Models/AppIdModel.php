<?php


namespace App\Models;


class AppIdModel extends BaseModel
{
    protected $table = 'app_id';

    // 使用create方法添加时，需判断
    protected $fillable = ['app_name', 'app_key', 'app_expire', 'stitching_symbol', 'description', 'status'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
