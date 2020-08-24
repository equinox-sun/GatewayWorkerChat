<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/5/20 15:19
// +----------------------------------------------------------------------
// | TITLE: 聊天信息处理
// +----------------------------------------------------------------------

namespace app\home\model;


use think\Model;
use think\Db;

class ChatMessage extends Model
{
    public static function getMsgList($customer_id)
    {
        return Db::name('chat_log')
            ->where('customer_id',$customer_id)
            ->order('record_time','desc')
            ->select();
    }

    public static function getCustomerList($staff_id)
    {
        return Db::name('users')
        	->field('user_id,user_name')
            ->where('staff_id',$staff_id)
            ->where('is_delete',0)
            ->select();
    }


    public static function getRecentCustomerList($time)
    {
        $customer_ids = Db::name('chat_log')
            ->where('record_time','>=',$time)
            ->group('customer_id')
            ->order('id','desc')
            ->column('customer_id');

        return Db::name('users')
            ->field('user_id,user_name')
            ->where('user_id','in',$customer_ids)
            ->where('is_delete',0)
            ->select();


    }

    public static function readMsg($customer_id)
    {
        return Db::name('chat_log')
            ->where('customer_id',$customer_id)
            ->where('from_staff',1)
            ->update([
                'has_read'=>1
            ]);
    }

    public static function hasNewMsg($customer_id)
    {
        return Db::name('chat_log')
            ->where('customer_id',$customer_id)
            ->where('from_staff',1)
            ->where('has_read',0)
            ->count();
    }


    public static function readCustomerMsg($customer_id)
    {
        return Db::name('chat_log')
            ->where('customer_id',$customer_id)
            ->where('from_staff',0)
            ->update([
                'has_read'=>1
            ]);
    }

    public static function staffHasNewMsg($staff_id)
    {
        return Db::name('chat_log')
            ->where('staff_id',$staff_id)
            ->where('from_staff',0)
            ->where('has_read',0)
            ->count();
    }
}