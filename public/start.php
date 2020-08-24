<?php
/**
 * workerman + GatewayWorker
 * 此文件只能在Linux运行
 * run with command
 * php start.php start
 */


use Workerman\Worker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;

ini_set('display_errors', 'on');
if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows.\n");
}
//检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}
if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (DIRECTORY_SEPARATOR === '/') {
    // linux:
    $workermanDir = '/workerman';
    $gatewayDir   = '/gateway-worker/src';
} else {
    // windows:
    $workermanDir = '/workerman-for-win';
    $gatewayDir   = '/gateway-worker-for-win/src';
}

$vendorDir = __DIR__ . '/../vendor';
// 自动加载类
$loader = require_once $vendorDir . '/autoload.php';

// 重新设置 Workerman 相关路径
$loader->setPsr4('Workerman\\', $vendorDir. '/workerman'. $workermanDir);
$loader->setPsr4('GatewayWorker\\', $vendorDir .'/workerman'. $gatewayDir);

//gateway自定义目录
$gatewayappDir = __DIR__ . '/../application/gatewayapp';

//加载配置文件
include_once $gatewayappDir.'/const.php';

//初始化register register 服务必须是text协议
$registerAddress = sprintf('text://%s',GW_REGISTER_PROTOCOL);
$register = new Register($registerAddress);

//初始化 bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = GW_WORKER_NAME;
// bussinessWorker进程数量
$worker->count = GW_BUSINESS_WORKER_COUNT;
// 服务注册地址
$worker->registerAddress = GW_REGISTER_ADDRESS;
//设置处理业务的类,此处制定Events的命名空间
$worker->eventHandler = GW_BUSINESS_EVENT_HANDLER;

// 初始化 gateway 进程
$gatewayAddress = sprintf('websocket://%s',GW_GATEWAY_ADDRESS);
$gateway = new Gateway($gatewayAddress);
// 设置名称，方便status时查看
$gateway->name = GW_GATEWAY_NAME;
$gateway->count = GW_GATEWAY_COUNT;
// 分布式部署时请设置成内网ip（非127.0.0.1）
$gateway->lanIp = GW_LOCAL_HOST_IP;
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
$gateway->startPort = GW_GATEWAY_START_PORT;
// 心跳间隔
//        $gateway->pingInterval = 10;
// 心跳数据
//        $gateway->pingData = '{"type":"ping"}';
// 服务注册地址
$gateway->registerAddress = GW_REGISTER_ADDRESS;

//运行所有Worker;
Worker::runAll();