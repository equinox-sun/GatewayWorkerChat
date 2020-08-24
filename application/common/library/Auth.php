<?php

namespace app\common\library;

use app\common\library\Token;
use app\common\model\UserRule;
use app\api\model\Admin;
use think\Request;

class Auth
{

    protected static $instance = null;
    protected $_error = '';
    protected $_logined = FALSE;
    protected $_user = NULL;
    protected $_user_id = 0;
    protected $_user_name = "";
    protected $_token = '';
    protected $keeptime = 0;
    protected $requestUri = '';
    protected $rules = [];
    //默认配置
    protected $config = [];
    protected $options = [];
    protected $allowFields = ['admin_id', 'username', 'nickname', 'avatar', 'email'];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     * 
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 兼容调用user模型的属性
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user[$name] : NULL;
    }

    /**
     * 根据Token初始化
     *
     * @param string       $token    Token
     * @return boolean
     */
    public function init($token)
    {
        $data = Token::get($token);
        if (empty($data))
        {
            $this->setError('请先登录');
            return FALSE;
        }
        
        if ($data['status'] != 1)
        {
            $this->setError('账号异常');
            return FALSE;
        }
        $this->_user = $data;
        $this->_user_id = $data['admin_id'];
        $this->_user_name = $data['username'];
        $this->_logined = TRUE;
        $this->_token = $token;
        
        return TRUE;
    }

    /**
     * 判断是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->_logined)
        {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取会员基本信息
     */
    public function getUserinfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $userinfo = array_intersect_key($data, array_flip($allowFields));
        $userinfo['token'] = $this->getToken();
        return $userinfo;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取允许输出的字段
     * @return array
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出的字段
     * @param array $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 设置会话有效时间
     * @param int $keeptime 默认为永久
     */
    public function keeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr)
        {
            return FALSE;
        }
        // 是否存在,不区分大小写
        if (in_array(strtolower($request->action()), array_map('strtolower',$arr)) || in_array('*', $arr))
        {
            return TRUE;
        }

        // 没找到匹配
        return FALSE;
    }

    /**
     * 检测是否是否有对应权限
     * @param string $path      控制器/方法
     * @param string $module    模块 默认为当前模块
     * @return boolean
     */
    public function check($path = NULL, $module = NULL)
    {
        //判断是否登陆
        if (!$this->_logined)
            return false;
        if($this->_user_name == "admin")
            return true;

        $ruleList = $this->getRuleList();
        $rules = [];
        foreach ($ruleList as $k => $v)
        {
            $rules[] = $v['rule_code'];
        }
//        print_r($rules);exit;
//        $url = ($module ? $module : request()->module()) . '/' . (is_null($path) ? $this->getRequestUri() : $path);
        $url = strtolower(is_null($path) ? $this->getRequestUri() : $path);
        $rules = array_map('strtolower', array_map("trim",$rules));
        return in_array($url, $rules) ? TRUE : FALSE;
    }

    /**
     * 获取用户组别规则列表
     * @return array
     */
    public function getRuleList()
    {
        if ($this->rules)
            return $this->rules;
        $user_rules = Admin::getUserRules($this->_user_id);
        if (!$user_rules)
        {
            return [];
        }
        $this->rules = UserRule::where('status', '=', 1)->where('rule_id', 'in', $user_rules)->field('rule_id,parent_id,rule_name,rule_code,is_menu')->select();

        return $this->rules;
    }
}
