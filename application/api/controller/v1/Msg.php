<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/5/21 11:16
// +----------------------------------------------------------------------
// | TITLE: 
// +----------------------------------------------------------------------

namespace app\api\controller\v1;


use app\api\controller\Api;
use app\home\model\ChatMessage;
use think\Exception;
use think\Validate;


class Msg extends Api
{
    public static $valid_days = 7;

    //消息列表
    public function msgList()
    {
        try {
            $json = $this->request->param('data');

            $params = json_decode($json,true);
           
            $page_size = isset($params['page_size'])?$params['page_size']:10;
            $page_index = isset($params['page_index'])?$params['page_index']:1;
            $offset = $page_size * ($page_index-1);
            // $customer_list = ChatMessage::getCustomerList($this->auth->getUser()['admin_id']);
            $customer_list = ChatMessage::getRecentCustomerList($offset,$page_size);
            $customer_arr = array_column($customer_list,null, 'user_id');
            $data = array();
            if (!empty($customer_arr)) {
                foreach (array_keys($customer_arr) as $user_id) {
                    $value = $customer_arr[$user_id];
                    $value['msg_list'] = array_reverse(ChatMessage::getMsgList($user_id));
                    $data[]=$value;
                }
            }
            
            $this->result($data,200, \think\Config::get('zh-cn_api.data_success'));
        } catch (Exception $e) {
            $this->result('',402, $e->getMessage());
        }
    }

    public function getCustomerList()
    {
        $json = $this->request->param('data');

        $params = json_decode($json,true);
       
        $time = time()-self::$valid_days*24*3600;
        $customer_list = ChatMessage::getRecentCustomerList($time);
        $this->result($customer_list,200, \think\Config::get('zh-cn_api.data_success'));
    }


    public function getCustomerMsg()
    {
        $json = $this->request->param('data');

        $params = json_decode($json,true);
        if(!isset($params['customer_id'])) $this->result('',400, \think\Config::get('zh-cn_api.customer_id_empty'));
        // $page_size = isset($params['page_size'])?$params['page_size']:20;
        // $page_index = isset($params['page_index'])?$params['page_index']:1;
        // $offset = $page_size * ($page_index-1);

        $this->result(array_reverse(ChatMessage::getMsgList($params['customer_id'])),200, \think\Config::get('zh-cn_api.data_success'));
    }

    public function readCustomerMsg()
    {
        $json = $this->request->param('data');

        $params = json_decode($json,true);
        if(!isset($params['customer_id'])) $this->result('',400, \think\Config::get('zh-cn_api.customer_id_empty'));

        $this->result(ChatMessage::readCustomerMsg($params['customer_id']),200, \think\Config::get('zh-cn_api.data_success'));
    }
}