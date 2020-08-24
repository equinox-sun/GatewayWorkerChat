<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::resource(':version/user','api/:version.User');   //注册一个资源路由，对应restful各个方法,.为目录

Route::any(':version/auth/:data','api/:version.Auth/add',['method'=>'get|post']);
Route::rule(':version/user/:id/fans','api/:version.User/fans'); //restful方法中除restful api外的其他方法路由
Route::rule(':version/token/wechat','api/:version.Token/wechat');
Route::rule(':version/token/mobile','api/:version.Token/mobile');
// Route::miss('Error/index'); //当没有匹配到所有的路由规则后,会分配到这个指定的路由里面,但是如果开启/的访问则检测不到，必须使用.的方式访问
return [
    '__pattern__' => [
        'name' => '\w+',
    ],   
];
