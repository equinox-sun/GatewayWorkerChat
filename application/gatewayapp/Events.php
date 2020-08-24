<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/5/8 17:09
// +----------------------------------------------------------------------
// | TITLE: 用户客服聊天业务逻辑处理
// +----------------------------------------------------------------------

use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
use GatewayWorker\Lib\DataManager;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events{

    public static $USER_PRE = 'user_';
    public static $STAFF_PRE = 'staff_';
    public static $STAFF_GROUP = 'staff_group';
    public static $CUSTOMER_GROUP = 'customer_group';

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * @param int $client_id 连接id
     */
     /*public static function onConnect($client_id) {
        // 向当前client_id发送数据
        Gateway::sendToClient($client_id, sprintf('Hello %s',$client_id));
        // 向所有人发送
        Gateway::sendToAll(sprintf('用户 %s 已登录！',$client_id));
    }*/

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    // public static function onMessage($client_id, $message) {
    //     // 向所有人发送
    //     Gateway::sendToAll(sprintf('用户 %s 说：%s',$client_id,$message));
    // }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    // public static function onClose($client_id) {
    //     // 向所有人发送
    //     GateWay::sendToAll(sprintf('用户 %s 已退出！',$client_id));
    // }


    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message)
    {
        // debug
        // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n\n\n";

        // 客户端传递的是json数据
        // echo($message);
        $message_data = json_decode($message, true);
        if(!$message_data || !isset($message_data['type']))
        {
            return '';
        }

        $type = intval($message_data['type']);//0-登录，1-文字，2-商品，3-图片，4-文件
        if ($type>4 || $type<0) {
            return self::sendErrorMsg('incorrect_type',$type);
        }
        if ($type!=0 && empty($_SESSION)) {
            return self::sendErrorMsg('please_login',$type);//没有先请求登录
        }

        $dm = self::onConnectDB();
        // 根据类型执行不同的业务
        switch($type)
        {
            // 客户端登录 message格式改为:  {type:0, token:xx,is_staff:0} 
            case 0:
                $is_staff = isset($message_data['is_staff']) ? intval($message_data['is_staff']) : 0;//客服标识

                $dm = self::onConnectDB();
                if (!array_key_exists('token', $message_data)) return self::sendErrorMsg('token_empty',$type);
                if ($is_staff) { //判断是否是客服
                    $staff_info = $dm->getStaffByToken($message_data['token']);
                    if (empty($staff_info)) return self::sendErrorMsg('token_error',$type);
                    // var_dump($staff_info);
                    $uid = $staff_info['admin_id'];
                    $_SESSION['uid']=$uid;
                    $_SESSION['user_name']=$staff_info['nickname'];
                    $relevant_uid = self::$STAFF_PRE.$uid;
                    $_SESSION['relevant_uid']=$relevant_uid;
                    Gateway::bindUid($client_id, $relevant_uid);//将client_id与relevant_uid绑定，以便通过Gateway::sendToUid($relevant_uid)发送数据
                    //加入客服组
                    Gateway::joinGroup($client_id, self::$STAFF_GROUP);//加入客服分组
                    //获取客户组
                    $customer_session_list = Gateway::getClientSessionsByGroup(self::$CUSTOMER_GROUP);
                    $customer_list = [];
                    if (!empty($customer_session_list)) {
                        foreach ($customer_session_list as $k => $v) {
                            $customer_list[] = array('user_id'=>$v['uid'],'user_name'=>$v['user_name']);
                        }
                    }
                    self::sendSuccessRes('login_success',$type,$customer_list);
                }else{//用戶
                    $userinfo = $dm->getUserByToken($message_data['token']);
                    if (empty($userinfo)){return self::sendErrorMsg('token_error',$type);} 
                    // var_dump($userinfo);
                    $uid = $userinfo['user_id'];
                    $_SESSION['client_id']=$client_id;
                    $_SESSION['uid']=$uid;
                    $_SESSION['user_name']=$userinfo['user_name'];
                    $relevant_uid = self::$USER_PRE.$uid;
                    $_SESSION['relevant_uid']=$relevant_uid;
                    Gateway::bindUid($client_id, $relevant_uid);//将client_id与relevant_uid绑定，以便通过Gateway::sendToUid($relevant_uid)发送数据
                    //加入客户组
                    Gateway::joinGroup($client_id, self::$CUSTOMER_GROUP);//加入客服分组
                    //告知客服组
                    Gateway::sendToGroup( self::$STAFF_GROUP,json_encode(array_merge($_SESSION,['type'=>-1])));
                    self::sendSuccessRes('login_success',$type);
                }
                return;
            break;
                
            // 客户端发言 message: {type:say, is_staff:0, content:xx,toUid:xx}
            // 发送的消息类型，1-文字，2-商品，3-图片，4-文件
            case 1:
                $content = nl2br(htmlspecialchars($message_data['content']));
                break;
            case 2:
                $goods_id = intval($message_data['content']);
                if (!$goods_id) return self::sendErrorMsg('goods_id_number',$type);//商品id必须是数字
                $goods_name = $dm->getGoodsName($goods_id);
                if (!$goods_name) return self::sendErrorMsg('data_failed',$type);//获取数据失败

                $goods_img = $dm->getGoodsImage($goods_id);
                $message_data['content'] = ["goods_id"=>$goods_id,"goods_name"=>$goods_name,"goods_img"=>$goods_img];
                $content = json_encode($message_data['content'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                break;
            case 3:
                $content = $message_data['content'];
                $ext = explode('.', $content)[1];
                if (!in_array(strtolower($ext), ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                    return self::sendErrorMsg('image_extension',$type);
                }
                break;
            case 4:
                $content = $message_data['content'];
                $ext = explode('.', $content)[1];
                if (!in_array(strtolower($ext), ['doc','docx','xls','xlsx','pdf'])) {
                    return self::sendErrorMsg('file_extension',$type);
                }
                break;
        }

        $is_staff = strpos($_SESSION['relevant_uid'], self::$USER_PRE)===false ? 1:0;//客服標識
        $chat_msg = array('msg_content'=>$message_data['content'],'type'=>$type,'from_staff'=>$is_staff?1:0);
        if ($is_staff) { //以下为客服操作
            //没有选择对应的客户
            if (!array_key_exists('toUid', $message_data) || !$message_data['toUid']) {
                return self::sendErrorMsg('toUid_empty',$type);//没有先请求登录
            }
            $chat_msg['customer_id']=$message_data['toUid'];
            $customer_relevant_uid = self::$USER_PRE.$message_data['toUid'];
            //记录message
            //1为给用戶，0为给客服发送消息
            $dm->recordMsg($_SESSION['uid'], $message_data['toUid'], 1, $content, $type);
        } else {
            $chat_msg['customer_id']=$_SESSION['uid'];
            $chat_msg['customer_user_name']=$_SESSION['user_name'];
            $customer_relevant_uid = $_SESSION['relevant_uid'];
            //记录message
            //1为给用戶，0为给客服发送消息
            $dm->recordMsg(0, $_SESSION['uid'], 0, $content, $type);
        }
        Gateway::sendToUid($customer_relevant_uid, json_encode($chat_msg,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        Gateway::sendToGroup(self::$STAFF_GROUP,json_encode($chat_msg,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // 先不处理
        // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id, session:".json_encode($_SESSION)."  onClose:''\n";
    }

    /**
     * BusinessWorker进程启动时触发（此特性Gateway版本>=2.0.4才支持）
     */
    public static function onWorkerStart(){
        // echo "WorkerStart\n";
    }


    public static function onConnectDB()
    {
        include_once 'const.php';
        return new DataManager(MySQL_Host,MySQL_Port,MySQL_User,MySQL_Pwd,MySQL_DB);
    }

    public static function sendErrorMsg($msg_key,$type)
    {
        $zh_cn_api = require dirname(__FILE__) .'/../extra/zh-cn_api.php';
        if (!array_key_exists($msg_key, $zh_cn_api)) {
            return Gateway::sendToCurrentClient(json_encode(['code'=>400,'type'=>$type,'msg'=>'msg_key error']));
        }
        Gateway::sendToCurrentClient(json_encode(['code'=>400,'type'=>$type,'msg'=>$zh_cn_api[$msg_key]]));
    }

    public static function sendSuccessRes($msg_key,$type,$data=array())
    {
        $zh_cn_api = require dirname(__FILE__) .'/../extra/zh-cn_api.php';
        if (!array_key_exists($msg_key, $zh_cn_api)) {
            return Gateway::sendToCurrentClient(json_encode(['code'=>200,'type'=>$type,'msg'=>'success']));
        }
        Gateway::sendToCurrentClient(json_encode(['code'=>200,'type'=>$type,'msg'=>$zh_cn_api[$msg_key],'data'=>$data]));
    }
}
