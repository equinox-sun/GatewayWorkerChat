<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/23 11:29
// +----------------------------------------------------------------------
// | TITLE: 前台用户日志记录
// +----------------------------------------------------------------------

namespace app\home\model;

use think\Model;

class UserLog extends Model
{
    protected $table = 'odm_user_log';

    /**
     * 新增一条品牌信息
     */
    public static function insertLog($array)
    {
        $array['createtime'] = time();
        return self::create($array);
    }
}