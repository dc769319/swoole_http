<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR);
$host = '127.0.0.1';
$port = 9951;
$server = new Charles\HttpServer(
    $host,
    $port,
    SWOOLE_PROCESS,
    SWOOLE_SOCK_TCP
);
$logPath = APP_PATH . 'swoole_http_server.log';
$server->set([
    'daemonize' => 1,
    'reactor_num' => 2,
    'worker_num' => 4,    //worker process num
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'dispatch_mode' => 3,
    'heartbeat_check_interval' => 30,
    'heartbeat_idle_time' => 60,
    'log_file' => $logPath
]);

$server->on('start', [$server, 'onStart']);

$server->on('managerStart', [$server, 'onManagerStart']);

$server->on('workerStart', [$server, 'onWorkerStart']);

$server->on('request', [$server, 'onRequest']);

$server->on('close', [$server, 'onClose']);

$server->on('workerError', [$server, 'onWorkerError']);

$server->start();
