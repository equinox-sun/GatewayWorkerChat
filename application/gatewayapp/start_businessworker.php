<?php
use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use Workerman\Autoloader;
use app\gatewayapp\controller\Events;

$businessInstance = new GwBusinessWorker();
call_user_func_array(array($businessInstance,'index'),array());

class GwBusinessWorker {

    public function __construct() {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        include_once dirname(__FILE__).'/const.php';
    }

    public function index() {
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = GW_WORKER_NAME;
        // bussinessWorker进程数量
        $worker->count = GW_BUSINESS_WORKER_COUNT;
        // 服务注册地址
        $worker->registerAddress = GW_REGISTER_ADDRESS;
        //设置处理业务的类,此处制定Events的命名空间
        // $worker->eventHandler = 'Events';

        // 如果不是在根目录启动，则运行runAll方法
        if(!defined('GLOBAL_START')) {
            Worker::runAll();
        }
    }

}