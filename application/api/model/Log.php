<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/2
 * Time: 11:06
 * 品牌管理
 */

namespace app\api\model;
use think\Model;
use think\Db;
class Log extends Model
{
    protected $table = 'odm_admin_log';

    /**
     * 新增一条品牌信息
     */
    public static function insertLog($array)
    {
        $array['createtime'] = time();
        return self::create($array);
    }

    //
}
