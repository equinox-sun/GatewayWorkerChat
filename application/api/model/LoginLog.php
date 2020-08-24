<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/13 16:56
// +----------------------------------------------------------------------
// | TITLE: 后台用户登录日志记录
// +----------------------------------------------------------------------

namespace app\api\model;


use think\Model;
use think\Db;

class LoginLog extends Model
{
    public static function addLoginLog($ip,$admin_id)
    {
        $insertData['login_time'] = time();
        $insertData['ip'] = $ip;
        $insertData['city'] = self::getCity($ip);
        $insertData['browser'] = self::getBrowser();
        $insertData['admin_id'] = $admin_id;
        return Db::name('login_log')->insert($insertData);
    }

    //获取浏览器名称
    public static function getBrowser()
    {
        $sys = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($sys, "NetCaptor") > 0) {
            $exp[0] = "NetCaptor";
        } elseif (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp= "Mozilla Firefox";

        } elseif (stripos($sys, "MAXTHON") > 0) {
            preg_match("/MAXTHON\s+([^;)]+)+/i", $sys, $b);
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp = $b[0] . " (IE" . $ie[1] . ")";
        } elseif (stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp = "Internet Explorer";

        } elseif (stripos($sys, "Netscape") > 0) {
            $exp = "Netscape";
        } elseif (stripos($sys, "Opera") > 0) {
            $exp = "Opera";

        } elseif (stripos($sys, "Chrome") > 0) {
            $exp = "Chrome";
        } else {
            $exp = "未知浏览器";
        }
        return $exp;
    }

    //根据ip获取所在城市
    public static function getCity($ip)
    {
        //TODO 暂时免费试用，后期考虑充值
        $token = 'f470e2e56912c26731b345f316339cea';
        $url = 'http://api.ip138.com/query/?ip='.$ip.'&token='.$token;
        $data = json_decode(self::curl_post($url),true);

        if ($data['ret'] == 'ok') {
            if ($data['data'][0] == '保留地址') {
                $city = '保留地址';
            } else {
                $city = $data['data'][1].$data['data'][2];
            }

        } else {
            $city = '未知';
        }
        return $city;
    }

    public static function curl_post($url)
    {
        $ch = curl_init($url);

        $this_header = array(
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //登录日志列表
    public static function loginList($params)
    {
        //总数
        $count = Db::name('login_log')
            ->count();
        $list = Db::name('login_log')
            ->order(['id'=>'DESC'])
            ->page($params['page_no'],$params['page_size'])
            ->field('id,ip,city,browser,login_time')
            ->select();

        return ["totalNumber" => $count, "list" => $list];
    }

}