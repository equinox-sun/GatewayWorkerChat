<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/5/8 17:09
// +----------------------------------------------------------------------
// | TITLE: 用户客服聊天业务逻辑处理
// +----------------------------------------------------------------------

// namespace app\gatewayapp\controller;

use GatewayWorker\Lib\Gateway;
use GatewayWorker\Lib\DataManager;
use Workerman\Lib\Timer;


/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class GwEvents {

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect     *
     * @param int $client_id 连接id
     */
     public static function onConnect($client_id) {
        // 向当前client_id发送数据
        Gateway::sendToClient($client_id, sprintf('Hello %s',$client_id));
        // 向所有人发送
        Gateway::sendToAll(sprintf('用户 %s 已登录！',$client_id));
    }

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
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";

        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }

        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                //************注意默认将用户设置成房间号***************************//
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $iskefu = isset($message_data['iskefu']) ? intval($message_data['iskefu']) : 0;//客服标识
                $client_name = htmlspecialchars($message_data['username']);//默认为房间
                $customer_service_name = isset($message_data['custom_service_name']) ? $message_data['custom_service_name'] : '客服小姐姐';
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;

                $dm = new DataManager();
                $dm->Db()->beginTrans();
                try {
                    if (!$iskefu) {//判断是否是客服，以下为用戶的操作
                        //将所有用户的$client_id和client_name放入房间好统一获取及分辨client_id是那个用户的
                        $_SESSION['client_room'][$client_id] = $client_name;
                        $data = [];
                        $data['ip'] = $_SERVER['REMOTE_ADDR'];
                        $data['last_login'] = time();
                        $data['is_online'] = 1;
                        if (empty($dm->isExists($client_name))) {//判断用戶是否记录过
                            $data['user'] = $client_name;
                            $data['record_time'] = $data['last_login'];
                            $kefuinfo = $dm->getKefuOne();
                            $data['kefu'] = (!empty($kefuinfo)) ? $kefuinfo['kefu'] : "";
                            $data['clients_id'] = $client_id;//记录用户client_id
                            $dm->insert('odm_chat_user', $data);//将用户记录到数据库
                            $dm->setKefuUserNum($data['kefu']);//设置客服对接的用户数，为方便获取对接少的用户的客服
                        } else {
                            $userinfo = $dm->getUserByUser($client_name);
                            $data['kefu'] = $dm->isHaveKefu($client_name);//判断是否有客服且是否在线有则返回无则重新获取一个在线客服并返回
                            $data['clients_id'] = (($userinfo['clients_id'] !== "") ? $userinfo['clients_id'].',' : '').$client_id;//记录用户client_id
                            $dm->save('odm_chat_user', $data,"`user`='{$client_name}'");//修改用戶数据
                            $dm->setKefuUserNum($data['kefu']);//设置客服对接的用户数，为方便获取对接少的用户的客服
                        }
                        $dm->Db()->commitTrans();
                        $new_message = [
                            'type'=>$message_data['type'],
                            'client_id'=>htmlspecialchars($client_name),
                            'client_name'=>htmlspecialchars($client_name),
                            'time'=>date('Y-m-d H:i:s')
                        ];
                        Gateway::joinGroup($client_id, $client_name);//这里以$client_name为房间
                        $kefuinfo = $dm->getKefuByKefu($data['kefu']);
                        if (!empty($kefuinfo)) {
                            //获取客服client_id
                            $clients_id = (strpos($kefuinfo['clients_id'], ",")) ? explode(",", $kefuinfo['clients_id']) : [$kefuinfo['clients_id']];
                            foreach ($clients_id as $key=>$val) {
                                if ($val) {
                                    Gateway::joinGroup($val, $client_name);//将客服的client_id加入用户房间
                                }
                            }
                            $new_message['kefu_user_name'] = $data['kefu'];
                        }


                        //给客服发送用户列表
                        //----------------start-----------------//
                        $res = $dm->getUserByKefu($customer_service_name);
                        $user_list = [];
                        foreach ($res as $key => $val) {
                            $user_list[$key]['username'] = $val['user'];//我這裏默認用戶為房間號
                        }
                        $clientList['type'] = 'login';
                        $clientList['client_list'] = $user_list;
                        Gateway::sendToGroup($customer_service_name, json_encode($clientList));
                        //-----------------end---------------------//

                      //TODO 以下注释的代码前端暂时不需要推送
                     /*   Gateway::sendToGroup($client_name, json_encode($new_message),$client_id);
                        Gateway::sendToCurrentClient(json_encode($new_message));*/

                    } else { //以下客服的操作
                        //将所有客服的$client_id和client_name放入Session好统一获取及分辨client_id是那个客服的
                        //示例：{"type":"login","username":"客服小姐姐","room_id":"1","iskefu":1}
                        $_SESSION['kefu_room'][$client_id] = $client_name; //客服房间默认为客服名称
                        Gateway::joinGroup($client_id, $client_name);//将client_id加入进客服本身的房间，而不是用户的房间，用于后面获取客服所有的client_id
                        $res = $dm->getUserByKefu($client_name);
                        //以下为客服下的用戶列表
                        $user_list = [];
                        foreach ($res as $key => $val) {
                            $user_list[$key]['username'] = $val['user'];//我這裏默認用戶為房間號
                            Gateway::joinGroup($client_id, $val['user']);//將客服客戶端加入房間//這裏需要重新加入到房間因爲client_id已經刷新了

                            //TODO 以下注释的代码前端暂时不需要推送
                           /* $new_message = [
                                'type' => $message_data['type'],
                                'customer_service_username' => htmlspecialchars($client_name),
                                'client_user_name' => htmlspecialchars($val['user']),
                                'time' => date('Y-m-d H:i:s')
                            ];
                            Gateway::sendToGroup($val['user'], json_encode($new_message),$client_id);*/

                        }

                        //获取未接待用戶并设置
                        $res=$dm->setOnlineUserKefu($client_name);//获取了5条并设置
                        foreach ($res as $key=>$val) {
                            if ($val) {
                                Gateway::joinGroup($client_id, $val['user']);//将客服客户端加入房间
                            }
                        }

                        //获取房间内所有用户列表，这里我将默认客服有一个房间，且所有客服的client_id房间为该客服的房间
                        //为方便用户进来时，将客服的client_id加入到房间去
                        $clients_list = Gateway::getClientSessionsByGroup($client_name);
                        $clients_id = [];
                        foreach ($clients_list as $tmp_client_id=>$item) {
                            if (isset($item['client_name'])) {
                                $clients_id[$tmp_client_id] = $item['client_name'];
                            }
                        }
                        $clients_id[$client_id] = $client_name;
                        $dm->saveKefuClientId($clients_id,$client_name);
                        $dm->Db()->commitTrans();
                        //获取客服客戶端用戶列表

                        //TODO 以下注释的代码前端暂时不需要推送
                        // 给当前用户发送用户列表
                       /* $new_message = [
                            'type' => $message_data['type'],
                            'user_id' => htmlspecialchars($client_name),
                            'user_name'=>htmlspecialchars($client_name),
                            'time'=>date('Y-m-d H:i:s')];
                        $new_message['client_list'] = $user_list;
                        Gateway::sendToCurrentClient(json_encode($new_message));*/

                    }

                }catch (Exception $e){
                    echo "错误异常".$e;
                    $dm->Db()->rollBackTrans();
                }
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                $room_id = $_SESSION['room_id'];
                $client_name = htmlspecialchars($message_data['username']);
                $iskefu = isset($message_data['iskefu'])?intval($message_data['iskefu']):0;//客服標識
                $new_message = [
                    'type'=>'say',
                    'from_user_id'=>$client_name,//当前用户或者客服在发消息
                    'from_user_name' =>$client_name,
                    'to_user_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'goods_detail' => isset($message_data['goods_detail']) ? nl2br(htmlspecialchars($message_data['goods_detail'])) : '',
                    'time'=>date('Y-m-d H:i:s')
                ];
                $dm = new DataManager();
                if ($iskefu) { //以下为客服操作
                    //客服对用户说或者用户发消息也是要发给自己，在自己的浏览器上记录消息，
                    $to_client_name = htmlspecialchars($message_data['to_username']);
                    //所以这里是不管是谁在发，都是要发给用户的，所以这里写死
                    $new_message['to_username'] = $to_client_name;
                    //记录message
                    //1为给用戶，2为给客服发送消息
                    $dm->recordMsg($to_client_name, $client_name, nl2br(htmlspecialchars($message_data['content'])), 1);

                    return Gateway::sendToGroup($to_client_name ,json_encode($new_message));
                } else {
                    //这里是不管是谁在发，都是要发给用户的，所以这里写死
                    $to_client_name = $client_name;
                    $new_message['to_username'] = $to_client_name;
                    //记录message
                    $userinfo = $dm->getUserByUser($client_name);
                    //用户推送的商品详情
                    $goods_detail = isset($message_data['goods_detail']) ? $message_data['goods_detail'] : '';
                    //1为给用戶，2为给客服 发送消息
                    $dm->recordMsg($client_name, $userinfo['kefu'], nl2br(htmlspecialchars($message_data['content'])), 2 , $goods_detail);

                    return Gateway::sendToGroup($client_name ,json_encode($new_message));
                }
                

        }
    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
        $iskefu = 0;
        $clients_list = isset($_SESSION['client_room'])?$_SESSION['client_room']:((isset($_SESSION['kefu_room'])&& $iskefu=1)?$_SESSION['kefu_room']:array());
        $dm = new DataManager();
        if(empty($clients_list[$client_id]))return;
        $dm->setClientsIdAndIsOnline($client_id, $clients_list[$client_id], $iskefu);
    }

    /**
     * BusinessWorker进程启动时触发（此特性Gateway版本>=2.0.4才支持）
     */
    public static function onWorkerStart(){
        echo "WorkerStart\n";
        //设置一个定时器，3个小时执行一次
        $dm = new DataManager();
        //判断用户是否10800
        Timer::add(1800, [$dm,'updateUserIsOline']);
    }

}
