<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/23 13:31
// +----------------------------------------------------------------------
// | TITLE: 用户登录，注册
// +----------------------------------------------------------------------

namespace app\home\controller\v1;


use app\home\model\Users;
use think\Controller;
use think\Validate;
use think\Config;


class Login extends Controller
{
    public function login()
    {
        $json = $this->request->param('data');

        $array = json_decode($json,true);
        //验证数据
        $rule = [
            'username' => 'require|email',
            'password' => 'require|min:6',
        ];

        $msg = [
            'username.require' => Config::get('zh-cn_api.username_empty'),      //用户名不能为空
            'username.email' => Config::get('zh-cn_api.must_email'),      //用户名必须是邮箱
            'password.require' => Config::get('zh-cn_api.pwd_empty'),      //密码不能为空
            'password.min' => Config::get('zh-cn_api.pwd_length'),      //密码长度不符合要求
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if (!$res) {
            $this->error($this->validate->getError(),'login');
        }


        $username = $array['username'];
        $password = $array['password'];

        $ret =  Users::userLogin($username, $password);
        //判断是否属于账号、密码类的错误
        switch ($ret) {
            case 401:
                $this->result($ret, '401', Config::get('zh-cn_api.account_error'));     //帐号不正确
                break;
            case 403:
                $this->result($ret, '403', Config::get('zh-cn_api.pwd_error'));       //密码不正确
                break;
            case 405:
                $this->result($ret, '405', Config::get('zh-cn_api.activate_account_send'));  //账号未激活，已发送邮件，请激活账户
                break;
            default:
                $this->result($ret, '200', Config::get('zh-cn_api.login_success'));    //登陆成功
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
            'access_token.require' => Config::get('zh-cn_api.token_empty'),

        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($params);
        if (!$res) {
            $this->error($this->validate->getError(),'logout');
        }
        $ret =  Users::logout($params['access_token'], $pk = 'user_id');
        if ($ret !== false) {
            $this->result($ret, '200', Config::get('zh-cn_api.logout_success'));
        } else {
            $this->result([], '401', Config::get('zh-cn_api.logout_failed'));
        }
    }

}