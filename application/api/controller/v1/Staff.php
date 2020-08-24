<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/13 15:00
// +----------------------------------------------------------------------
// | TITLE: 后台会员管理模块
// +----------------------------------------------------------------------

namespace app\api\controller\v1;

use app\common\model\StaffCommon;
use think\Controller;
use think\Validate;

class Staff extends Controller
{

    //会员登录
    public function login()
    {
        $json = $this->request->param('data');

        $array = json_decode($json,true);
        //验证数据
        $rule = [
            'username' => 'require|min:1',
            'password' => 'require|min:6',
        ];

        $msg = [
            'username.require' => '请填写用户名',
            'username.min' => '用户名长度不符合要求',
            'password.require' => '请填写密码',
            'password.min' => '密码长度不符合要求',
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if (!$res) {
            $this->error($this->validate->getError(),'login');
        }


        $username = $array['username'];
        $password = $array['password'];

        $ret =  (new StaffCommon)->login($username, $password);
        //判断是否属于账号、密码类的错误
        switch ($ret) {
            case 401:
                $this->result($ret, '401', '账号不正确');
                break;
            case 400:
                $this->result($ret, '400', '账号异常，已被禁止登录');
                break;
            case 403:
                $this->result($ret, '403', '密码不正确');
                break;
            case false:
                $this->result([], 401, '登陆失败！');
                break;
            default:
                $this->result($ret, '200', '登录成功！');

        }



    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $params = $this->request->param();
        //验证数据
        $rule = [
            'access_token' => 'require',

        ];

        $msg = [
            'access_token.require' => '请填写access_token',

        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($params);
        if (!$res) {
            $this->error($this->validate->getError(),'login');
        }
        $ret =  (new StaffCommon)->logout($params['access_token']);
        if ($ret !== false) {
            $this->result($ret, '200', '注销成功');
        } else {
            $this->result([], 401, '注销失败');
        }
    }

}