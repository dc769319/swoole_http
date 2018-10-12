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
     * @param string $errMsg 错误信息
     * @return bool|int
     */
    public static function add(
        string $message,
        string $title,
        string $logFile,
        string &$errMsg = ''
    ) {
        if (empty($logFile)) {
            $errMsg = 'Invalid param logPath';
            return false;
        }
        if (strncmp('/', $logFile, 1) !== 0) {
            if (!defined('APP_PATH')) {
                $errMsg = 'Empty constant APP_PATH';
                return false;
            }
            //去掉末尾的.log
            rtrim($logFile, '.log');
            $logFile = APP_PATH . self::LOG_DIR . DIRECTORY_SEPARATOR . $logFile . '.log';
        }
        $date = date('[Y-m-d H:i:s]');
        $log = sprintf("%s%s | %s%s", $date, $title, $message, PHP_EOL);
        return file_put_contents($logFile, $log, FILE_APPEND);
    }
}
