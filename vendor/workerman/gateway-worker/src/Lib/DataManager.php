<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace GatewayWorker\Lib;

/**
 *
 */
class DataManager 
{
       static $db;
       
       public function __construct($host, $port, $user, $password, $db_name)
       {
           
          self::$db=new DbConnection($host, $port, $user, $password, $db_name);
       }
       
       public function Db()
       {
           return self::$db;
       }
       public static function insert($table,$data)
       {
             self::$db->insert($table)->cols($data)->query();
       }
       
       public static function saveExecute($table,$fileds,$where,$data)
       {
           self::$db->update($table)->bindValues($data)->cols($fileds)->where($where)->query();
       }
       
       public static function save($table,$data,$where=array())
       {
           if($table=="")return;
           if(empty($data))return;
           $fileds=array();
           foreach ($data as $key=>$val)
           {
               $fileds[]=$key;
           }
           if(empty($where))return;
           self::saveExecute($table, $fileds, $where, $data);
       }
       public static function select($table,$where)
       {
           
       }

       /************************************************  用户  ****************************************/
       /**
        * 根据token获取用户信息
        */
       public static function getUserByToken($token)
       {
          $result= self::$db->query('SELECT * FROM `odm_users` where `token`=\''.$token.'\' limit 1');
           return !empty($result)?$result[0]:array();
       }

       public function getOneStaffId()
       {
           $staff = self::getOneOnlineStaff();
           if (!empty($staff)) return $staff['admin_id'];//有在线客服则返回
           $staff = self::getRandStaff();
           return $staff['admin_id'];
       }
       /**
        * 獲取對接客服
        */
       public static function getOneOnlineStaff()
       {
           $sql="select * from odm_admin where is_online=1 order by customer_num asc limit 1";//获取到对接用户最少当中的一个在线客服
//            $sql="SELECT * FROM `odm_admin` where is_online=1  ORDER BY RAND() LIMIT 1";//随机获取一个在线客服
           $result=self::$db->query($sql);
           return !empty($result)?$result[0]:array();
       }

       public static function getRandStaff()
       {
           $sql="SELECT * FROM `odm_admin`  ORDER BY RAND() LIMIT 1";//随机获取一个客服
           $result=self::$db->query($sql);
           return $result[0];
       }

       /**
        * 判斷在綫用戶的對接客服是否在綫
        */
       public static function isStaffOnline($staff_id)
       {
           $sql="select is_online from odm_admin where admin_id=".$staff_id." limit 1";
           $result=self::$db->query($sql);
           return !empty($result)?$result[0]['is_online']:0;
       }

       /**
        * 获取一个对接客服的id
        */
       public static function getRelationStaffId($staff_id)
       {
           $is_online=self::isStaffOnline($staff_id);
           if ($is_online) return $staff_id;
           $staff = self::getOneOnlineStaff();
           return empty($staff['admin_id'])?$staff_id:$staff['admin_id'];
       }

       /**
        * 设置对接客服id
        */
       public static function setStaffId($uid,$staff_id)
       {
          $sql = "update `odm_users` set staff_id = ".$staff_id." where user_id=".$uid;
          return self::$db->query($sql);
       }

       /**
        * 設置客服對接用戶數
        * 如果操作过于频繁则考虑定时取客服对应的group组对接数更新
        * @param unknown $kefu
        */
       public static function setCustomerNum($staff_id,$num)
       {
            $sql = "update `odm_admin` set customer_num = ".$num." where admin_id = ".$staff_id;
            return self::$db->query($sql);
       }



       /***********************************************   客服  *****************************************/

       public static function getStaffByToken($token)
       {
          $result= self::$db->query('SELECT * FROM `odm_admin` where `token`=\''.$token.'\' limit 1');
           return !empty($result)?$result[0]:array();
       }

       public static function batchSetStaffId($user_id_list,$staff_id)
       {
          if (empty($user_id_list) || !$staff_id) {
            return 0;
          }
          $user_ids=implode(',', $user_id_list);
          $sql = "update `odm_users` set staff_id = ".$staff_id." where user_id in (".$user_ids.")";
          return self::$db->query($sql);
       }

       //记录发送的消息
       public function recordMsg($staff_id, $customer_id, $from_staff, $msg_content,$msg_type = 1)
       {
            $data = array(
              'staff_id' => $staff_id,
              'customer_id' => $customer_id,
              'from_staff' => $from_staff,
              'msg_content' => $msg_content,
              'msg_type' => $msg_type,
              'record_time' => time(),
            );
           return self::insert('odm_chat_log', $data);
       }
       
       //离线client_id后的处理，判断是否离线
       public function setClientsIdAndIsOnline($client_id,$client_name,$iskefu){
           if($iskefu){
               $kefuinfo=self::getKefuByKefu($client_name);
               if(empty($kefuinfo))return ;
               $clients_id=explode(",", $kefuinfo['clients_id']);
               if(in_array($client_id, $clients_id)){
                   $clients_id_=array_flip($clients_id);
                   unset($clients_id[$clients_id_[$client_id]]);
                   $kefuinfo['clients_id']=(count($clients_id)>0)?implode(",", $clients_id):"";
                   empty($clients_id) && $kefuinfo['is_online']=0;
                   self::save('odm_chat_kefu', $kefuinfo,"kefu='{$client_name}'");
               }
           }else{
               $useruinfo=self::getUserByUser($client_name);
               //关闭连接，修改用户在线状态设置成离线
               if (!empty($userInfo)) {
                   $sql = 'update odm_chat_user set is_online=0 where id ='.$userInfo['id'];
                   self::$db->query($sql);
               }
               // $clients_id=explode(",", $useruinfo['clients_id']);
               // if(in_array($client_id, $clients_id)){
               //     $clients_id_=array_flip($clients_id);
               //     unset($clients_id[$clients_id_[$client_id]]);
               //     $useruinfo['clients_id']=(count($clients_id)>0)?implode(",", $clients_id):"";
               //     empty($clients_id) && $useruinfo['is_online']=0;
               //     self::save('odm_chat_user', $useruinfo,"`user`='{$client_name}'");
               //  }
            }
       }
       
       /**
        *   判断用户在7个小时内是否有操作
        */
       public function updateUserIsOline(){
           $time=time()-25200;
           $sql="update odm_chat_user set clients_id='' ,is_online=0 where lastlogin<{$time}";
            self::$db->query($sql);
       }

       public function getGoodsName($goods_id)
       {
          $result = self::$db->query('select goods_name from odm_goods where goods_id = '.$goods_id.' and is_show=1 and is_delete=0 ');
          return !empty($result)?$result[0]['goods_name']:'';
       }

       public function getGoodsImage($goods_id)
       {
          $result = self::$db->query('select img_url from odm_goods_gallery where goods_id = '.$goods_id.' order by master_map desc');
          return !empty($result)?$result[0]['img_url']:'';
       }
       
}
