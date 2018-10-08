<?php
//引入composer
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
//定义常量
define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('HTTP_SERVER_PORT', 9951);
define('HTTP_SERVER_HOST', '127.0.0.1');
define('TCP_SERVER_PORT', 9952);
define('TCP_SERVER_HOST', '127.0.0.1');
//定义tcp包分解符
define('PACKAGE_EOF', "\r\n\r\n");
//设置时区
date_default_timezone_set("Asia/Shanghai");
//检查是否在cli模式运行
if (strncmp(php_sapi_name(), 'cli', 3) !== 0) {
    exit(sprintf("Error: please run this program in CLI model.%s", PHP_EOL));
}
if (!extension_loaded('swoole')) {
    exit(sprintf("Error: no swoole extension.%s", PHP_EOL));
}
