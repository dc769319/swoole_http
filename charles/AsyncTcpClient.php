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
     * @var array events 发送异步请求后，将请求标识作为key，回调方法作为value
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
        if (self::$reqNo > 200000) {
            //值一旦太大则重置为0
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
        self::$client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT, 5);
        //一段时间后，检查是否已经连接上
        swoole_timer_after(5000, function () use ($workerId) {
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
     * @param callable $onRecCallback 接收到服务端数据后的回调函数
     * @throws \Exception
     */
    public static function send(string $data, callable $onRecCallback)
    {
        $reqNo = self::getReqNo();
        self::$events[$reqNo] = $onRecCallback;
        $packedData = Protocol::encBySign($data, $reqNo);
        if (!(self::$client->isConnected())) {
            //如果连接断开，则尝试延时重连并发送
            swoole_timer_after(500, [__CLASS__, 'resend'], [$packedData, 0]);
        }
        if (false === self::$client->send($packedData)) {
            //发送失败，则记录错误日志
            self::log(
                json_encode([
                    'workerId' => self::$workerId,
                    'errCode' => self::$client->errCode,
                    'errMsg' => swoole_strerror(self::$client->errCode)
                ]),
                'send error'
            );
        }
    }

    /**
     * 重新发送
     * @param string $packedData
     * @param int $times
     * @throws \Exception
     */
    private static function resend(string $packedData, int $times)
    {
        $times++;
        if (self::$client->isConnected()) {
            self::$client->send($packedData);
        } else {
            if ($times < 3) {
                //重新建立连接
                self::$client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT, 5);
                swoole_timer_after(500, [__CLASS__, 'resend'], [$packedData, $times]);
            } else {
                //重新发送失败，记录错误日志
                self::log(sprintf('resend times: %d', $times), 'resend failed');
            }
        }
    }

    /**
     * 建立tcp连接后的回调
     * @param \Swoole\Client $client 异步客户端实例对象
     */
    public static function onConnect(\Swoole\Client $client)
    {
        //建立长连接，每隔一段时间发个包
        if (is_null(self::$pingTick)) {
            self::$pingTick = swoole_timer_tick(60000, function () {
                self::send('PING');
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

    /**
     * 记录日志
     * @param string $message
     * @param string $title
     * @throws \Exception
     */
    public static function log(string $message, string $title)
    {
        Log::add(
            $message,
            $title,
            'async_tcp_client'
        );
    }
}