<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'common_set.php';

$server = new Charles\HttpServer(
    HTTP_SERVER_HOST,
    HTTP_SERVER_PORT,
    SWOOLE_PROCESS,
    SWOOLE_SOCK_TCP
);
$logPath = APP_PATH . 'logs/swoole_http_server.log';
$server->set([
    'daemonize' => 1,
    'reactor_num' => 2,
    'worker_num' => 4,    //worker process num
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'dispatch_mode' => 3,
    'heartbeat_check_interval' => 30,
    'heartbeat_idle_time' => 60,
    'log_file' => $logPath,
    'chroot' => '/tmp/root', //切换到安全的目录
    'user' => 'www-data', //worker进程所属用户
    'group' => 'www-data' //worker进程所属组
]);

$server->on('start', [$server, 'onStart']);

$server->on('managerStart', [$server, 'onManagerStart']);

$server->on('workerStart', [$server, 'onWorkerStart']);

$server->on('request', [$server, 'onRequest']);

$server->on('close', [$server, 'onClose']);

$server->on('workerError', [$server, 'onWorkerError']);

$server->start();
