<?php

namespace app\common\library;

use think\Cache;

/**
 * Token操作类
 */
class Token
{
    
    // 缓存对象
    protected static $cache_obj = null;

    /**
     * 初始化Cache
     */
    public static function initCache()
    {
        if (is_null(self::$cache_obj))
        {
            self::$cache_obj = Cache::connect(config('token_location'));
        }
    }

    /**
     * 存储Token
     * @param   string    $token      Token
     * @param   array     $user       会员数据
     * @param   int       $expire     过期时长,0表示无限,单位秒
     * @param   string    $pk         用户表主键名称
     */
    public static function set($token, $user, $expire = 0, $pk = 'admin_id')
    {
        self::initCache();
        if (empty($token) || empty($user))
        {
            return false;
        }

        $user['token_settime'] = time();
        $expiretime = $expire ? time() + $expire : 0;
        // 如果登录过，则删除之前的缓存信息
        if ($old_token = self::$cache_obj->get($pk.$user[$pk]))
        {
            self::$cache_obj->rm($pk.$old_token);
        }
        self::$cache_obj->set($pk.$token, $user, $expiretime);
        self::$cache_obj->set($pk.$user[$pk], $token, $expiretime);
        return true;
    }

    /**
     * 获取Token内的信息
     * @param   string  $token 
     * @param   string  $pk         用户表主键名称
     * @return  array
     */
    public static function get($token, $pk = 'admin_id')
    {
        self::initCache();
        
        $user = self::$cache_obj->get($pk.$token);
        if (empty($token) || !isset($user[$pk]) || $user[$pk] < 1)
        {
            return [];
        }
        $cache_token = self::$cache_obj->get($pk.$user[$pk]);
        if ($cache_token != $token)
        {
            // 以cache_token为准，删除token对应的缓存
            self::$cache_obj->rm($pk.$token);
            return [];
        }
        if (config('set_token_expire'))
        {
            $token_expiretime = config('token_expiretime');
            if (isset($user['token_settime']) && $token_expiretime && $user['token_settime'] + $token_expiretime > time())
            {
                unset($user['token_settime']);
                return $user;
            } else {
                return [];
            }
        }
        
        unset($user['token_settime']);
        return $user;
    }
    
    /**
     * 删除Token
     * @param   string  $token
     * @return  boolean
     */
    public static function delete($token, $pk = 'admin_id')
    {
        if (empty($token))
        {
            return false;
        }
        self::initCache();
        
        $user = self::$cache_obj->get($pk.$token);
        self::$cache_obj->rm($pk.$token);
        
        if (isset($user[$pk]) && $user[$pk] > 0)
        {
            self::$cache_obj->rm($pk.$user[$pk]);
        }
        
        return true;
    }

}
