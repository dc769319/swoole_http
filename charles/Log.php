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
        $title,
        $logPath
    ) {
        if (empty($logPath)) {
            throw new \Exception('Invalid param logPath');
        }
        if (strncmp('/', $logPath, 1) !== 0) {
            if (!defined('APP_PATH')) {
                throw new \Exception('Empty constant APP_PATH');
            }
            $logPath = APP_PATH . $logPath;
        }
        $date = date('[Y-m-d H:i:s]');
        $log = sprintf("%s%s | %s%s", $date, $title, $message, PHP_EOL);
        file_put_contents($logPath, $log, FILE_APPEND);
    }
}
