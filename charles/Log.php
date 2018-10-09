<?php

namespace Charles;

/**
 * Class Log
 * @package Charles
 */
class Log
{
    const LOG_DIR = 'logs';

    /**
     * 添加日志
     * @param string $message
     * @param string $title
     * @param string $logFile 日志文件名称
     * @throws \Exception
     */
    public static function add(
        string $message,
        string $title,
        string $logFile
    ) {
        if (empty($logFile)) {
            throw new \Exception('Invalid param logPath');
        }
        if (strncmp('/', $logFile, 1) !== 0) {
            if (!defined('APP_PATH')) {
                throw new \Exception('Empty constant APP_PATH');
            }
            //去掉末尾的.log
            rtrim($logFile, '.log');
            $logFile = APP_PATH . self::LOG_DIR . DIRECTORY_SEPARATOR . $logFile . '.log';
        }
        $date = date('[Y-m-d H:i:s]');
        $log = sprintf("%s%s | %s%s", $date, $title, $message, PHP_EOL);
        file_put_contents($logFile, $log, FILE_APPEND);
    }
}
