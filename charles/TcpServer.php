<?php

namespace Charles;

/**
 * Class TcpServer
 */
class TcpServer extends \Swoole\Server
{
    /**
     * @var TcpHandler handler
     */
    public $handler = null;

    /**
     * @param TcpServer $server
     */
    public function onStart(TcpServer $server)
    {
        swoole_set_process_name('charles_tcp_master');
    }

    /**
     * @param TcpServer $server
     */
    public function onManagerStart(TcpServer $server)
    {
        swoole_set_process_name('charles_tcp_manager');
    }

    /**
     * @param TcpServer $server
     * @param int $workerId
     */
    public function onWorkerStart(TcpServer $server, int $workerId)
    {
        swoole_set_process_name('charles_tcp_worker');
        $this->handler = new TcpHandler();
    }

    /**
     * 接收到数据包后，回调
     * @param TcpServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     * @throws \Exception
     */
    public function onReceive(TcpServer $server, int $fd, int $reactorId, string $data)
    {
        $this->handler->handle($server, $data, $fd, $reactorId);
    }

    /**
     * @param TcpServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect(TcpServer $server, int $fd, int $reactorId)
    {
    }

    /**
     * @param TcpServer $server
     * @param int $fd
     * @param int $reactorId
     * @throws \Exception
     */
    public function onClose(TcpServer $server, int $fd, int $reactorId)
    {
        $this->log(
            sprintf('fid:%d, reactorId:%d', $fd, $reactorId),
            'Tcp server closed'
        );
    }

    /**
     * @param TcpServer $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     * @throws \Exception
     */
    public function onWorkerError(
        TcpServer $server,
        int $workerId,
        int $workerPid,
        int $exitCode,
        int $signal
    ) {
        $this->log(
            sprintf(
                'workerId:%d, workerPid:%d, exitCode:%d, signal:%d',
                $workerId,
                $workerPid,
                $exitCode,
                $signal
            ),
            'Worker error'
        );
    }

    /**
     * 添加日志
     * @param string $message
     * @param string $title
     * @throws \Exception
     */
    private function log($message, $title)
    {
        Log::add(
            $message,
            $title,
            'logs/tcp_server.log'
        );
    }
}
