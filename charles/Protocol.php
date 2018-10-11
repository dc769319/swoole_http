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
     * 文本协议前缀，左半部分
     */
    const PRE_LEFT = '##CH';

    /**
     * 文本协议前缀，右半部分
     */
    const PRE_RIGHT = '##';

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
        return self::PRE_LEFT . self::PRE_RIGHT . $data . PACKAGE_EOF;
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
        $head = self::PRE_LEFT . self::PRE_RIGHT;
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
        if ($sign < 1) {
            return false;
        }
        $head = sprintf("%s%d%s", self::PRE_LEFT, $sign, self::PRE_RIGHT);
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
        $headPattern = sprintf("%s(\d+)%s", self::PRE_LEFT, $sign, self::PRE_RIGHT);
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
