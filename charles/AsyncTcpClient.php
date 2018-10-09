<?php

namespace Charles;

/**
 * 异步Tcp客户端，基于\Swoole\Client
 * Class SyncTcpClient
 * @package Charles
 * @author dongchao
 * @email dongchao@bigo.sg
 */
class AsyncTcpClient
{

    /**
     * @var \Swoole\Client client
     */
    public static $client = null;

    /**
     * @var int workerId worker进程编号
     */
    public static $workerId = null;

    /**
     * @var int pingTick 毫秒定时器句柄
     */
    public static $pingTick = null;

    /**
     * @var array events 发送异步请求后，将请求标识保存到
     */
    public static $events = [];

    /**
     * @var int reqNo 请求编号
     */
    private static $reqNo = 0;

    /**
     * 获得请求编号
     */
    public static function getReqNo()
    {
        if (self::$reqNo > 2000000000) {
            self::$reqNo = 0;
        }
        return ++self::$reqNo;
    }

    /**
     * 初始化异步客户端
     * @param int $workerId worker编号
     */
    public static function init(int $workerId)
    {
        self::$workerId = $workerId;
        //创建异步tcp客户端
        self::$client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        self::$client->set([
            'open_eof_check' => true,
            'package_eof' => PACKAGE_EOF
        ]);
        self::$client->on('connect', [__CLASS__, 'onConnect']);
        self::$client->on('receive', [__CLASS__, 'onReceive']);
        self::$client->on('close', [__CLASS__, 'onClose']);
        self::$client->on('error', [__CLASS__, 'onError']);
        //异步客户端connect方法会立刻返回，不会阻塞
        self::$client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT, 10);
        //一段时间后，检查是否已经连接上
        swoole_timer_after(10000, function () use ($workerId) {
            if (!self::$client->isConnected()) {
                //连接超时，记录错误日志
                Log::add(
                    sprintf('worker id: %d', $workerId),
                    'connect timeout',
                    'async_tcp_client'
                );
            }
        });
    }

    /**
     * @param string $data 待发送数据
     * @return string|bool 服务端进程返回的数据，失败返回false
     */
    public static function send(string $data)
    {
        Protocol::encBySign($data);
    }

    /**
     * @param \Swoole\Client $client 异步客户端实例对象
     */
    public static function onConnect(\Swoole\Client $client)
    {
        //建立长连接，每隔一段时间发个包
        if (is_null(self::$pingTick)) {
            self::$pingTick = swoole_timer_tick(60000, function () {
            });
        }
    }

    /**
     * @param \Swoole\Client $client 异步客户端实例对象
     * @param string $data 接收到的数据
     */
    public static function onReceive(\Swoole\Client $client, string $data)
    {
    }

    /**
     * @param \Swoole\Client $client 异步客户端实例对象
     */
    public static function onClose(\Swoole\Client $client)
    {
    }

    /**
     * @param \Swoole\Client $client 异步客户端实例对象
     */
    public static function onError(\Swoole\Client $client)
    {
    }
}
