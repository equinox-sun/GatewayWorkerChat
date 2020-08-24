<?php
/**
 * 授权基类，所有获取access_token以及验证access_token 异常都在此类中完成
 */

namespace app\home\controller;

use think\Controller;
use app\common\library\Auth;
use app\home\model\UserLog;
use app\common\library\Token;

class Api extends Controller
{
    /**
     * 需要验证权限的方法，,而且需要登录
     * @var array
     */
    protected $needRight = [];

    use Send;

    // 验证类
    protected $auth;
    protected $_user = NULL;

    /**
     * 对应操作
     * @var array
     */
    public $methodToAction = [
        'get' => 'read',
        'post' => 'save',
        'put' => 'update',
        'delete' => 'delete',
        'patch' => 'patch',
        'head' => 'head',
        'options' => 'options',
    ];
    /**
     * 允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post|put|delete|patch|head|options';

    public $access_token;

    /**
     * 控制器初始化操作
     */
    public function _initialize()
    {
        $this->auth = Auth::instance();
        $access_token = $this->request->request('access_token') ?: $this->request->cookie('_l_token_');
        //验证Token的有效性
        $token = Token::get($access_token, 'user_id');
        if (empty($token)) {
            return $this->returnmsg(460, "Access_token expired or error！", "");
        }

        $this->_user = $token;
        $this->_user_id = $token['user_id'];
        $this->_account_number = $token['account_number'];
        $this->_user_name = $token['email'];
        $this->_logined = TRUE;
        $this->_token = $access_token;

        // 判断是否需要验证权限
        $controllername = explode("/", str_replace('.', '/', strtolower($this->request->controller())));
        $controllername = strtolower($controllername[1]);
        $actionname = strtolower($this->request->action());
        $path = $controllername . '/' . $actionname;

        // 判断是否需要验证权限，存在则需要验证
        if ($this->auth->match($this->needRight)) {
            // 判断控制器和方法判断是否有对应权限
            if (!$this->auth->check($path)) {
                return $this->returnmsg(461, "无权限操作！", "");
            }
        }

    }

    protected function autoValidate($rule = null)
    {
        $data = $this->request->param('data');
        if ($data) {
            $data = json_decode($data, true);
        }

        if ($rule) {
            $result = $this->validate($data, $rule);

            if ($result !== true) {
                $this->returnmsg(402, $result);
            }
        }

        return $data ?: [];
    }

}