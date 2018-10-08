<?php

namespace Charles;

/**
 * Class Log
 * @package Charles
 */
class Log
{
    /**
     * 添加日志
     * @param $message
     * @param $title
     * @param string $logPath
     * @throws \Exception
     */
    public static function add(
        $message,
        $title = '',
        $logPath = '/Users/charlesdong/Projects/PHP/swoole/http_server.log'
    ) {
        $date = date('[Y-m-d H:i:s]');
        $log = sprintf("%s%s | %s%s", $date, $title, $message, PHP_EOL);
        file_put_contents($logPath, $log, FILE_APPEND);
    }
}
