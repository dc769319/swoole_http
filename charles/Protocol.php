<?php

namespace Charles;

/**
 * TCP数据打包、解包协议
 * Class Proxy
 * @package Charles
 * @author dongchao
 * @email dongchao@bigo.sg
 */
class Protocol
{
    /**
     * @var string $headSign 文本协议头部标识
     */
    public static $headSign = '##CRSH##';

    /**
     * 打包数据
     * @param string $data
     * @return string
     */
    public static function encode(string $data)
    {
        if (empty($data)) {
            return $data;
        }
        return self::$headSign . $data . PACKAGE_EOF;
    }

    /**
     * 解包数据
     * @param string $data
     * @return string
     */
    public static function decode(string $data)
    {
        if (empty($data)) {
            return $data;
        }
        $head = self::$headSign;
        $pattern = "/^$head/";
        if (!preg_match($pattern, $data, $match)) {
            return '';
        }
        if (empty($match)) {
            return '';
        }
        return trim(substr($data, strlen($match[0])));
    }
}
