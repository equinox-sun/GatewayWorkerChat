<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;
use think\Config;
// 应用公共文件

/**
 * 在给定的时间上加上除去周6和周7的天数，返回时间戳
 * @param type $time 给定的时间
 * @param type $days 天数
 * @return boolean
 */
function getWorkTime($time, $days)
{
    $time = intval($time);
    $days = intval($days);
    if ($days == 0)
    {
        return $time;
    }
    for($i=0; $i<abs($days); $i++)
    {
        if ($days > 0)
        {
            $time += 24*3600;
        } else {
            $time -= 24*3600;
        }
        $w = date('w', $time);
        // 星期天和星期六不算
        if ($w == 0 || $w == 6)
        {
            $i--;
        }
    }

    return $time;
}

/**
 * 通过admin_id获取用户的信息
 * @param string|array $adminId  用户的admin_id
 * @return array|false|null|PDOStatement|string|\think\Model
 */
function userData($adminId)
{
    $centerData = Db::connect(Config::get('database.center'));
    $method = is_array($adminId) ? 'select' : 'find';
    $result = $centerData
        ->name('admin')
        ->field('nickname,telephone,email')
        ->where('admin_id', 'in', (array)$adminId)
        ->$method();
    return $result;
}

/**
 * 通过文档找到节点的所有审核人员
 * @param $params array (doc_id文档id、tpl_type为文档类型）
 * @return array|bool
 */
function auditPerson($params)
{
    $nodeId = '';
    if($params['tpl_type'] ==1) {
        $nodeId = Db::name('project_bom')->where(['bom_id'=>$params['doc_id']])->value('node_id');

    }elseif($params['tpl_type'] == 2) {
        $nodeId = Db::name('uitableview_default')->where(['ud_id'=>$params['doc_id']])->value('node_id');

    }elseif($params['tpl_type'] == 3) {
        $nodeId = Db::name('document')->where(['pd_id'=>$params['doc_id']])->value('node_id');

    }elseif($params['tpl_type'] == 4) {
        $nodeId = Db::name('form_data')->where(['id'=>$params['doc_id']])->value('node_id');

    }
    if($nodeId) {
      return nodeAudit($nodeId);
    }else{
        return false;
    }
}

/**
 * 查找节点审核人员
 * @param $nodeId
 * @return array|bool
 */
function nodeAudit($nodeId)
{
    $where['target_id'] = $nodeId;
    $where['target_type'] = 2;
    $where['role_type'] = 5;
    $auditor = \app\api\model\Relation::auditInfo($where);
    return $auditor;
}