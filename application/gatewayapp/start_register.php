<?php

use GatewayWorker\Register;
use Workerman\Worker;

$registerInstance = new GwRegister();
call_user_func_array(array($registerInstance,'index'),array());

class GwRegister {

    public function __construct() {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        include_once dirname(__FILE__).'/const.php';
    }

    public function index() {
        // register 必须是text协议
        $registerAddress = sprintf('text://%s',GW_REGISTER_PROTOCOL);
        $register = new Register($registerAddress);

        // 如果不是在根目录启动，则运行runAll方法
        if(!defined('GLOBAL_START')) {
            Worker::runAll();
        }
    }

}