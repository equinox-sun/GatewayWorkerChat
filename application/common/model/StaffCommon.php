<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/13 15:05
// +----------------------------------------------------------------------
// | TITLE: 
// +----------------------------------------------------------------------

namespace app\common\model;


use app\api\model\LoginLog;
use think\Model;
use think\Db;
use think\Hook;
use app\common\model\StaffModel;
use app\common\library\Token;

class StaffCommon extends Model
{
    protected $_user = NULL;
    protected $_token = '';

    /**
     * 添加用户
     * @param $params array 添加用户的信息
     * @param $role_id int 角色id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function addStaff($params, $role_id)
    {
        //加密密码
        $params['password'] = $this->getEncryptPassword($params['password'], $params['salt']);

        //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $params['note'] = !empty($params['note']) ? $params['note'] : null;
            $user = StaffModel::create($params);
            //管理员数量加一
            Db::name('admin_role')->where(['role_id' => $role_id])->setInc('admin_count');
            //添加绑定用户角色关联信息
            Db::name('admin_role_relation')
                ->insert(['admin_id' => $user->admin_id, 'role_id' => $role_id, 'create_time' => time()]);

            Db::commit();

            // 此时的Model中只包含部分数据
            $this->_user = StaffModel::get($user->admin_id);
            //设置Token
            $this->_token = Random::uuid();
            \app\common\library\Token::set($this->_token,$this->_user->toArray());

            //注册成功的事件
            Hook::listen("user_register_successed", $this->_user);

            return ['token'=>$this->_token,'admin_id' =>$user->admin_id];
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
    }

    public  function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 用户登录
     *
     * @param string    $username   账号,用户名、邮箱、手机号
     * @param string    $password   密码
     * @return array
     */
    public function login($username, $password)
    {
        $user = StaffModel::get(['username' => $username]);
        if (!$user)
        {
            $this->setError('账号不正确');
            return 401;
        }

        if ($user->status != 1)
        {
            $this->setError('账号异常，已被禁止登录', '204');
            return 400;
        }
        if ($user->password != $this->getEncryptPassword($password, $user->salt))
        {
            $this->setError('密码不正确');
            return 403;
        }

        //直接登录会员
        return $this->direct($user->admin_id);

    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     */
    public function setError($error, $error_no = '0')
    {
        $this->_error_no = $error_no;
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取会员基本信息
     */
    public function getUserinfo()
    {
        $data = $this->_user->toArray();
        $allowFields = ['admin_id', 'username', 'nickname', 'avatar', 'email'];
        $userinfo = array_intersect_key($data, array_flip($allowFields));
        $userinfo['token'] = $this->getToken();
        return $userinfo;
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
     * 直接登录账号
     * @param int $user_id
     * @return boolean
     */
    public function direct($user_id)
    {
        $user = StaffModel::get($user_id);

        if ($user) {
            $token = \app\common\model\Random::uuid();
            //记录本次登录的IP和时间
            $user->ip = request()->ip();
            $user->logintime = time();
            $user->token = $token;
            $user->is_online = 1;
            $user->save();
            /*   $user["department_info"] = Db::name('department')->where("did = ".$user["department_id"])->find();
               $user["job_info"] = Db::name('job')->where("jid = ".$user["job_id"])->find();*/
            $this->_user = $user;
            $this->_token = $token;
            Token::set($this->_token, $user->toArray());
            $this->_logined = TRUE;

            //登录成功的事件
            Hook::listen("user_login_successed", $this->_user);
            //登录记录日志
            LoginLog::addLoginLog($user->ip,$user->admin_id);

            //返回用户信息
            $data = $this->getUserinfo();
            return $data;
        } else {
            return false;
        }
    }


    /**
     * 注销登录
     * @param $access_token
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function logout($access_token, $pk = 'admin_id')
    {
        $data= StaffModel::get(['token' => $access_token]);
        //删除Token
        if ($data) {
            $data->token = '';
            $data->is_online = 0;
            return $data->save();
        }
        //删除Token
        Token::delete($access_token, $pk);
        return TRUE;
    }

    public function changePassword($params)
    {
        $ignoreOldPassword = false;
        $old_password = $params['old_password'];
        $new_password = $params['new_password'];
        $admin_id = $params['admin_id'];
        // 此时的Model中只包含部分数据
        $this->_user = StaffModel::get($admin_id);
        //判断旧密码是否正确
        if ($this->_user->password == $this->getEncryptPassword($old_password, $this->_user->salt) || $ignoreOldPassword) {
            $salt = Random::alnum();
            $new_password = $this->getEncryptPassword($new_password, $salt);
            $this->_user->save(['password' => $new_password, 'salt' => $salt,'email' => $params['email']]);
            //重新设置缓存
            Token::set($params['access_token'],$this->_user->toArray());
            //修改密码成功的事件
            Hook::listen("user_changepwd_successed", $this->_user);
            return true;
        } else {
            return false;
        }
    }

    //重置密码
    public function resetPassword($params)
    {
        $new_password = $params['new_password'];
        $admin_id = $params['admin_id'];
        // 此时的Model中只包含部分数据
        $this->_user = StaffModel::get($admin_id);
        $salt = Random::alnum();
        $new_password = $this->getEncryptPassword($new_password, $salt);

        return  $this->_user->save(['password' => $new_password, 'salt' => $salt]);

        //重新设置缓存
  //      Token::set($params['access_token'],$this->_user->toArray());
        //修改密码成功的事件
//        Hook::listen("user_changepwd_successed", $this->_user);
    }

    //编辑用户
    public function editUser($params)
    {
        $admin_id = $params['admin_id'];
        $list['username'] = $params['username'];
        $list['nickname'] = $params['nickname'];
        $list['email'] = $params['email'];
        $list['telephone'] = $params['telephone'];
        $list['note'] = isset($params['telephone']) && !empty($params['note']) ? $params['note'] : null;

        $this->_user = StaffModel::get($admin_id);
        $this->_user->save($list);
        //修改管理员与角色绑定关系
        Db::name('admin_role_relation')->where(['admin_id' => $admin_id])->update(['role_id' => $params['role_id'],'update_time' => time()]);
        //重新设置缓存
//        Token::set($params['access_token'],$this->_user->toArray());
        //修改密码成功的事件
//        Hook::listen("user_changepwd_successed", $this->_user);
        return true;
    }

}