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
     * @var string $head 文本协议头部字符串
     */
    public static $head = '##CH##';

    /**
     * @var string headBySign 文本协议头部字符串，中间插入标识
     */
    public static $headBySign = '##CH|##';

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
        return self::$head . $data . PACKAGE_EOF;
    }

    /**
     * 解包数据
     * @param string $data
     * @return string|bool
     */
    public static function decode(string $data)
    {
        if (empty($data)) {
            return $data;
        }
        $head = self::$head;
        $pattern = "/^$head/";
        if (!preg_match($pattern, $data, $match)) {
            return false;
        }
        if (empty($match)) {
            return false;
        }
        return trim(substr($data, strlen($match[0])));
    }

    /**
     * 打包数据，并添加额外的标识
     * @param string $data 待打包数据
     * @param int $sign 标识
     * @return string
     */
    public static function encBySign(string $data, int $sign)
    {
        $headSign = explode('|', self::$headBySign);
        if (sizeof($headSign) < 2) {
            return false;
        }
        if ($sign < 1) {
            return false;
        }
        $head = sprintf("%s%d%s", $headSign[0], $sign, $headSign[1]);
        return $head . $data . PACKAGE_EOF;
    }

    /**
     * 解包数据，解析数据并且设置打包时增加的标识
     * @param string $data 待打包数据
     * @param int $sign 标识
     * @return bool|string
     */
    public static function decBySign(string $data, int &$sign)
    {
        $headSign = explode('|', self::$headBySign);
        if (sizeof($headSign) < 2) {
            return false;
        }
        $headPattern = sprintf("%s(\d+)%s", $headSign[0], $headSign[1]);
        if (!preg_match("/^$headPattern/", $data, $match)) {
            return false;
        }
        if (empty($match) || (sizeof($match) < 2)) {
            return false;
        }
        $sign = intval($match[1]);
        return trim(substr($data, strlen($match[0])));
    }
}
