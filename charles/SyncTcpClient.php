<?php

namespace Charles;

/**
 * 同步Tcp客户端，基于\Swoole\Client
 * Class SyncTcpClient
 * @package Charles
 * @author dongchao
 * @email dongchao@bigo.sg
 */
class SyncTcpClient
{
    /**
     * @param string $data 待发送数据
     * @return string|bool 服务端进程返回的数据，失败返回false
     * @throws \Exception
     */
    public static function send(string $data)
    {
        //创建同步tcp客户端
        $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $client->set([
            'open_eof_check' => true,
            'package_eof' => PACKAGE_EOF
        ]);
        if (!$client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT, 10)) {
            $errCode = $client->errCode;
            $errMsg = swoole_strerror($errCode);
            Log::add(
                sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                'TCP Server connect error',
                'sync_tcp_server'
            );
            return false;
        }
        $package = Protocol::encode($data);
        if (!$client->send($package)) {
            $errCode = $client->errCode;
            $errMsg = swoole_strerror($errCode);
            Log::add(
                sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                'Package send error',
                'sync_tcp_server'
            );
            return false;
        }
        $result = $client->recv();
        if (false === $result) {
            $errCode = $client->errCode;
            $errMsg = swoole_strerror($errCode);
            Log::add(
                sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                'Package send error',
                'sync_tcp_server'
            );
            return false;
        }
        //关闭tcp连接
        $client->close();
        //服务端进程发送来的数据包，采用统一的协议打包，这里进行解包
        return Protocol::decode($result);
    }
}
