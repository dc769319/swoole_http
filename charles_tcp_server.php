<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'common_set.php';

$server = new Charles\TcpServer(
    TCP_SERVER_HOST,
    TCP_SERVER_PORT,
    SWOOLE_PROCESS,
    SWOOLE_SOCK_TCP
);
$logPath = APP_PATH . 'logs/swoole_tcp_server.log';

$server->set([
    'daemonize' => 1,
    'reactor_num' => 2,
    'worker_num' => 4,    //worker process num
    'task_worker_num' => 2,
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'dispatch_mode' => 3,
    'heartbeat_check_interval' => 60,
    'heartbeat_idle_time' => 300,
    'log_file' => $logPath,
    'open_eof_check' => true,
    'package_eof' => PACKAGE_EOF,
    'package_max_length' => 1024 * 1024 * 1, //1M
    'chroot' => '/tmp/root', //切换到安全的目录
    'user' => 'www-data', //worker进程所属用户
    'group' => 'www-data' //worker进程所属组
]);

$server->on('start', [$server, 'onStart']);

$server->on('managerStart', [$server, 'onManagerStart']);

$server->on('workerStart', [$server, 'onWorkerStart']);

$server->on('connect', [$server, 'onConnect']);

$server->on('receive', [$server, 'onReceive']);

$server->on('close', [$server, 'onClose']);

$server->on('workerError', [$server, 'onWorkerError']);

$server->on('task', ['\\Charles\\TaskHandler', 'task']);

$server->on('finish', ['\\Charles\\TaskHandler', 'finish']);

$server->start();
