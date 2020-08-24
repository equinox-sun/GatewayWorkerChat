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
    public static $GROUP_PRE = 'group_';

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
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n\n\n";

        // 客户端传递的是json数据
        // echo($message);
        $message_data = json_decode($message, true);
        // var_dump($message_data);
        if(!$message_data)
        {
            return '';
        }

        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式改为:  {type:login, token:xx,is_staff:0} 去掉房间号
            case 'login':
                $is_staff = isset($message_data['is_staff']) ? intval($message_data['is_staff']) : 0;//客服标识

                $dm = self::onConnectDB();
                if (!array_key_exists('token', $message_data)) return self::sendErrorMsg('token_empty','login');
                if ($is_staff) { //判断是否是客服
                    $staff_info = $dm->getStaffByToken($message_data['token']);
                    if (empty($staff_info)) return self::sendErrorMsg('token_error','login');
                    var_dump($staff_info);
                    $uid = $staff_info['admin_id'];
                    $_SESSION['uid']=$uid;
                    $_SESSION['user_name']=$staff_info['nickname'];
                    $relevant_uid = self::$STAFF_PRE.$uid;
                    $_SESSION['relevant_uid']=$relevant_uid;
                    Gateway::bindUid($client_id, $relevant_uid);//将client_id与relevant_uid绑定，以便通过Gateway::sendToUid($relevant_uid)发送数据
                    $group_name = self::$GROUP_PRE.$uid;//以客服id作为分组依据
                    $all_client_info = Gateway::getAllClientSessions();
                    var_dump($all_client_info);
                    $i=0;
                    $user_id_list=[];
                    foreach ($all_client_info as $k => $v) {
                        if (!empty($v)) {
                            //获取未接待用戶并设置
                            if (strpos(self::$USER_PRE, $v['relevant_uid'])!==false && ( !array_key_exists('staff_id', $v) || $v['staff_id']==0)) {
                                Gateway::joinGroup($k, $group_name);//加入客服分组
                                Gateway::updateSession($k, array('staff_id'=>$uid));
                                $user_id_list[]=$v['uid'];
                                $i++;
                            }
                        }
                        if ($i==5) break;//获取了5条并设置
                    }
                    $customer_list_info = Gateway::getClientSessionsByGroup($group_name);
                    $cutomer_list_clientid = Gateway::getClientIdListByGroup($group_name);
                    $cutomer_list_id = Gateway::getUidListByGroup($group_name);
                    echo "\n getClientSessionsByGroup:";
                    var_dump($customer_list_info);
                    echo "\ngetClientIdListByGroup:";
                    var_dump($cutomer_list_clientid);
                    echo "\ngetUidListByGroup:";
                    var_dump($cutomer_list_id);
                    //修改数据库表
                    $dm->Db()->beginTrans();
                    try {
                        if (!empty($user_id_list)) $res=$dm->batchSetStaffId($user_id_list,$uid);
                        $num = Gateway::getUidCountByGroup($group_name);
                        $dm->setCustomerNum($uid,$num); 
                    } catch (Exception $e) {
                        $dm->Db()->rollBackTrans();
                        return self::sendErrorMsg('operation_failed','login');
                    }

                    $dm->Db()->commitTrans();
                    self::sendSuccessRes('login_success','login',array_values($cutomer_list_id));
                }else{//用戶
                    $userinfo = $dm->getUserByToken($message_data['token']);
                    if (empty($userinfo)){return self::sendErrorMsg('token_error','login');} 
                    var_dump($userinfo);
                    $uid = $userinfo['user_id'];
                    $_SESSION['client_id']=$client_id;
                    $_SESSION['uid']=$uid;
                    $_SESSION['user_name']=$userinfo['user_name'];
                    $relevant_uid = self::$USER_PRE.$uid;
                    $_SESSION['relevant_uid']=$relevant_uid;
                    Gateway::bindUid($client_id, $relevant_uid);//将client_id与relevant_uid绑定，以便通过Gateway::sendToUid($relevant_uid)发送数据
                    if (!$userinfo['staff_id']) {//用戶没聊天过
                        $staff_id = $dm->getOneStaffId();
                        if (empty($staff_id)) return self::sendErrorMsg('operation_failed','login');
                    } else {
                        //先在gateway上判断是否在线
                        $staff_uid = self::$STAFF_PRE.$userinfo['staff_id'];
                        if(!Gateway::isUidOnline($staff_uid)){
                            // 不在线则查库判断是否已登录，已登录则返回原id，不然重新获取一个，如果没有在线的客服则返回原id
                            $staff_id = $dm->getRelationStaffId($userinfo['staff_id']);
                        }
                        else{
                            $staff_id = $userinfo['staff_id'];
                        }
                    }
                    $_SESSION['staff_id']=$staff_id;//客服id

                    $dm->Db()->beginTrans();
                    try {
                        //设置对接客服id，将客户client_id加入客服的组中，修改客服服务数量，给客服推送用户
                        $dm->setStaffId($uid,$staff_id); 
                        $group_name = self::$GROUP_PRE.$staff_id;//以客服id作为分组依据
                        Gateway::joinGroup($client_id, $group_name);//加入客服分组
                        $num = Gateway::getUidCountByGroup($group_name);//获取组内在线成员数
                        $dm->setCustomerNum($staff_id,$num); //更新数据库中客服对接客户数
                    } catch (Exception $e) {
                        $dm->Db()->rollBackTrans();
                        return self::sendErrorMsg('operation_failed','login');
                    }
                    
                    $dm->Db()->commitTrans();
                    Gateway::sendToUid(self::$STAFF_PRE.$staff_id, json_encode(array_merge($_SESSION,['type'=>'add_customer'])));//告知客服
                    self::sendSuccessRes('login_success','login');
                }
            return;
                
            // 客户端发言 message: {type:say, is_staff:0, content:xx,goods_id:xx,toUid:xx}
            case 'say':
                if (empty($_SESSION)) {
                    // echo "string";
                    return self::sendErrorMsg('please_login','say');//没有先请求登录
                }
                var_dump($_SESSION);
                $is_staff = strpos($_SESSION['relevant_uid'], self::$USER_PRE)===false ? 1:0;//客服標識
                $chat_msg = array('content'=>$message_data['content'],'type'=>'say','from_staff'=>$is_staff?1:0);
                $dm = self::onConnectDB();
                if ($is_staff) { //以下为客服操作
                    //没有选择对应的客户
                    if (!array_key_exists('toUid', $message_data) || !$message_data['toUid']) {
                        return self::sendErrorMsg('toUid_empty','say');//没有先请求登录
                    }
                    $chat_msg['customer_id']=$message_data['toUid'];
                    //记录message
                    //1为给用戶，2为给客服发送消息
                    var_dump([$_SESSION['uid'], $message_data['toUid'], 1, nl2br(htmlspecialchars($message_data['content']))]);
                    $dm->recordMsg($_SESSION['uid'], $message_data['toUid'], 1, nl2br(htmlspecialchars($message_data['content'])));
                    $relevant_uid_arr = [self::$USER_PRE.$message_data['toUid'] , $_SESSION['relevant_uid']];
                } else {
                    //没有选择对应的客服
                    if (!array_key_exists('staff_id', $_SESSION) || !$_SESSION['staff_id']) {
                        $staff_id = $dm->getOneStaffId();
                        $_SESSION['staff_id']=$staff_id;
                    }
                    $chat_msg['customer_id']=$_SESSION['uid'];
                    //记录message
                    //1为给用戶，2为给客服发送消息
                    $goods_id = isset($message_data['goods_id']) ? intval($message_data['goods_id']) : 0;
                    var_dump([$_SESSION['staff_id'], $_SESSION['uid'], 2, nl2br(htmlspecialchars($message_data['content'])), $goods_id]);
                    echo $dm->recordMsg($_SESSION['staff_id'], $_SESSION['uid'], 2, nl2br(htmlspecialchars($message_data['content'])), $goods_id);
                    $relevant_uid_arr = [$_SESSION['relevant_uid'], self::$STAFF_PRE.$_SESSION['staff_id']];
                    $goods_id && $chat_msg['goods_id']=$goods_id;
                }
                var_dump($relevant_uid_arr);
                echo "\n\n\n";
                Gateway::sendToUid($relevant_uid_arr, json_encode($chat_msg));
        }
    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // 先不处理
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id, session:".json_encode($_SESSION)."  onClose:''\n";
        $is_staff = strpos($_SESSION['relevant_uid'], self::$USER_PRE)===false ? 1:0;//客服標識
        if ($is_staff) {
            //获取客服对应的组
        }else{

        }
    }

    /**
     * BusinessWorker进程启动时触发（此特性Gateway版本>=2.0.4才支持）
     */
    public static function onWorkerStart(){
        echo "WorkerStart\n";
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
