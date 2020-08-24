<?php
// 注册协议
define('GW_REGISTER_PROTOCOL','127.0.0.1:1236');

// 注册地址
define('GW_REGISTER_ADDRESS','127.0.0.1:1236');

// 网关地址
define('GW_GATEWAY_ADDRESS','127.0.0.1:8283');

// 网关起始端口
define('GW_GATEWAY_START_PORT','2900');

// 心跳检测间隔，单位：秒，0 表示不发送心跳检测
define('GW_GATEWAY_PING_INTERVAL',10);

// 本机ip，分布式部署时请设置成内网ip（非127.0.0.1），无论如何都不能写0.0.0.0
define('GW_LOCAL_HOST_IP','127.0.0.1');

// 网关名称
define('GW_GATEWAY_NAME','Gateway001');

// worker进程名称
define('GW_WORKER_NAME','BusinessWorker001');

// Gateway进程数量，建议与CPU核数相同
define('GW_GATEWAY_COUNT',2);

// BusinessWorker进程数量，建议设置为CPU核数的1倍-3倍
define('GW_BUSINESS_WORKER_COUNT',6);

// Business业务处理类，可以带命名空间
define('GW_BUSINESS_EVENT_HANDLER','Events');

// MySQL相关配置
define('MySQL_Host','192.168.80.240');

define('MySQL_Port',3306);

define('MySQL_User','root');

define('MySQL_Pwd','root');

define('MySQL_DB','7000mall_123odm');