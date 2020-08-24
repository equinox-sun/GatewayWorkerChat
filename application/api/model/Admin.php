<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/4/2
 * Time: 14:59
 */
namespace app\api\model;

use app\common\library\Token;
use think\Db;
use think\Model;
use think\Config;
use plm\Random;

class Admin extends Model
{
    //获取用户列表
    public static function getList($params)
    {


        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;

        $where = 'status in (1,2)';

        if (isset($params['keywords']) && !empty($params['keywords'])) {
            $where .= ' AND username LIKE "%'.$params['keywords'].'%"';
            $where .= ' OR nickname LIKE "%'.$params['keywords'].'%"';
        }

        $sort = !empty($params['sort']) ? 'admin_id desc' : 'admin_id';

        $count = Db::name('admin')
            ->where($where)
            ->count();

        $result = Db::name('admin')
            ->field('admin_id,username,nickname,email,telephone,note,status,createtime,logintime')
            ->where($where)
            ->page($page, $limit)
            ->order($sort)
            ->select();

        //处理用户关联信息
        foreach ($result as $key => &$value) {
            if ($value['status'] === 2) {
                $result[$key]['status'] = 0;
            }
            $init = ["role_name"=>"","role_id" => ""];
            $admin_info = Db::name('admin_role_relation')
                ->alias('ai')
                ->field('d.role_name,d.role_id')
                ->join('admin_role d', 'ai.role_id = d.role_id', 'LEFT')
                ->where('ai.admin_id', $value['admin_id'])->find();
            $result[$key] = empty($admin_info) ? array_merge($value, $init) : array_merge($value, $admin_info);
        }

        $data = [
            'list' => $result,
            'page' => [
                'total_count' => $count,
                'current_page' => $params['page'],
                'page_size' => $params['limit'],
                'total_page' => ceil($count / $params['limit'])
            ],
        ];


        return $data;
    }
    

    //获取权限各ID
    public static function getUserRules($admin_id){
        $role_id = Db::name('admin_role_relation')->where('admin_id',$admin_id)->value('role_id');

        $rules = Db::name('admin_role_power_relation')
            ->field('power_id as rule_id')
            ->where('role_id', $role_id)
            ->find();

        return $rules;
    }

    //删除用户
    public static function deleteUser($admin_id)
    {
        return Db::name('admin')->where('admin_id',$admin_id)->update(['status'=>3, 'updatetime'=>time()]);
    }

    //后台首页信息
    public static function getHomeList()
    {
        $table = Db::name('order');
        $goodsTable = Db::name('goods');
        $userTable = Db::name('users');
        // $orderStatus = ' AND order_status = 1 AND pay_status = 7';
         $orderStatus = ' AND is_delete = 0 and order_status !=4';//排除已取消的订单
        //今日订单总数
        $todayCount = $table
            ->where('TO_DAYS(FROM_UNIXTIME(add_time)) = TO_DAYS(NOW())'.$orderStatus)
            ->count();
  //      $todayCount = Db::query('SELECT COUNT(*) AS todayCount FROM odm_order WHERE to_days(FROM_UNIXTIME(add_time)) = to_days(now())'.$orderStatus);
        //今日销售总额
        $todayAmountTotal = $table
            ->where('TO_DAYS(FROM_UNIXTIME(add_time)) = TO_DAYS(NOW())'.$orderStatus)
            ->sum('order_amount');

        //昨天销售总额
        $yesterdayAmountTotal = $table
            ->where('TO_DAYS(NOW()) - TO_DAYS(FROM_UNIXTIME(add_time)) = 1'.$orderStatus)
            ->sum('order_amount');

        //近七天销售总额
        $nearlyDaysTotal = $table
            ->where('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 7 DAY)) <= add_time'.$orderStatus)
            ->sum('order_amount');

        //待审核商品
        $unAuditGoodsCount = $goodsTable
            ->where('goods_status',0)
            ->where('is_delete',0)
            ->count();

        //待评估订单（定制）
        $unAuditCustomOrderCount = $table
            ->where(['order_status' => 0, 'order_type' => 1])
            ->where('is_delete',0)
            ->count();
        
        //待发货订单（定制）
        $pendingCustomOrderCount = $table
            ->where(['order_status' => 3, 'shipping_status' => 0 , 'pay_status' => 7, 'order_type' => 1])
            ->where('is_delete',0)
            ->count();
        //未上架商品
        $unListedGoods = $goodsTable->where(['goods_status' => 1, 'is_show' => 0])
            ->where('is_delete',0)->count();
        //已上架商品
        $goodsList = $goodsTable->where(['goods_status' => 1, 'is_show' => 1])
            ->where('is_delete',0)->count();
        //全部商品
        $allGoodsList = $goodsTable->where('is_delete',0)->count();
        //今日新增用户
        $todayAddUsers = $userTable
            ->where('TO_DAYS(FROM_UNIXTIME(create_time)) = TO_DAYS(NOW()) AND is_delete = 0')
            ->count();
        //昨日新增用户
        $yesterdayAddUsers = $userTable
            ->where('TO_DAYS(NOW()) - TO_DAYS(FROM_UNIXTIME(create_time)) = 1 AND is_delete = 0')
            ->count();
        //本月新增用户
        $thisMonthAddUsers = $userTable
            ->where('UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) <= create_time AND is_delete = 0')
            ->count();
        //所有用户
        $usersCount = $userTable->where(['is_delete' => 0])->count();

        $userOverview = [
            'todayAddUsers' => $todayAddUsers,
            'yesterdayAddUsers' => $yesterdayAddUsers,
            'thisMonthAddUsers' => $thisMonthAddUsers,
            'usersCount' => $usersCount,
        ];
        $orderList = [
            'todayCount' => $todayCount,
            'todayAmountTotal' => $todayAmountTotal,
            'yesterdayAmountTotal' => $yesterdayAmountTotal,
            'nearlyDaysTotal' =>$nearlyDaysTotal,
        ];
        $pendingTransaction = [
            'unAuditGoodsCount' => $unAuditGoodsCount,
            'unAuditCustomOrderCount' => $unAuditCustomOrderCount,
            'pendingCustomOrderCount' => $pendingCustomOrderCount,
        ];
        $goodsList = [
            'unListedGoods' => $unListedGoods,
            'goodsList' => $goodsList,
            'allGoodsList' => $allGoodsList,
        ];

        return $list = [
            'orderList' => $orderList,
            'pendingTransaction' => $pendingTransaction,
            'goodsList' => $goodsList,
            'userOverview' => $userOverview,
        ];
    }

}