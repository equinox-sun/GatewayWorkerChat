<?php
namespace app\gatewayapp\controller;
use GatewayWorker\Lib\Gateway;
use think\Request;

class Worker extends Server {
    public function __construct(){
        require_once __DIR__ . '/../../../autoload.php';
        $worker = new BusinessWorker();
        $worker->name = 'Business';
        $worker->count = 1;
        $worker->registerAddress = '127.0.0.1:1238';
        $worker->eventHandler = '\\app\\gatewayapp\\Events';
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    } 
    public static function onConnect($client_id) {
    }
    public static function onMessage($client_id, $message) {

    }
    public static function onClose($client_id) {
    }
}