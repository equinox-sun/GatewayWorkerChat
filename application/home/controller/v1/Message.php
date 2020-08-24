<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/5/20 15:14
// +----------------------------------------------------------------------
// | TITLE: 客户留言信息
// +----------------------------------------------------------------------

namespace app\home\controller\v1;

use app\home\controller\Api;
use app\home\model\ChatMessage;
use think\Exception;
use think\Validate;
use think\Config;


class Message extends Api
{
    //消息列表
    public function msgList()
    {
        try {
            $json = $this->request->param('data');
            $params = json_decode($json,true);

            $this->result(array_reverse(ChatMessage::getMsgList($this->_user['user_id'])),200, Config::get('zh-cn_api.data_success'));
        } catch (Exception $e) {
            $this->result('',402, $e->getMessage());
        }
    }

    public function readMsg()
    {
        $this->result(ChatMessage::readMsg($this->_user['user_id']),200, Config::get('zh-cn_api.operation_success'));
    }

    public function hasNewMsg()
    {
        $this->result(ChatMessage::hasNewMsg($this->_user['user_id']),200, Config::get('zh-cn_api.data_success'));
    }
}