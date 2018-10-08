<?php

namespace Charles;

/**
 * Class HttpServer
 */
class HttpServer extends \Swoole\Http\Server
{
    /**
     * @var HttpHandler
     */
    public $httpHandler = null;

    /**
     * @param $server HttpServer
     */
    public function onStart(HttpServer $server)
    {
        swoole_set_process_name('charles_http_master');
    }

    /**
     * @param $server HttpServer
     */
    public function onManagerStart(HttpServer $server)
    {
        swoole_set_process_name('charles_http_manager');
    }

    /**
     * @param HttpServer $server
     * @param int $workerId
     * @throws \Exception
     */
    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        swoole_set_process_name('charles_http_worker');
        $this->httpHandler = new HttpHandler();
    }

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @throws \Exception
     */
    public function onRequest($request, $response)
    {
        $this->httpHandler->onRequest($request, $response);
    }

    /**
     * @param HttpServer $server
     * @param int $fd
     * @param int $reactorId
     * @throws \Exception
     */
    public function onClose(HttpServer $server, int $fd, int $reactorId)
    {
        $this->log(
            sprintf('fid:%d, reactorId:%d', $fd, $reactorId),
            'Http server closed'
        );
    }

    /**
     * @param \Swoole\Http\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     * @throws \Exception
     */
    public function onWorkerError(
        \Swoole\Http\Server $server,
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
            'logs/http_server.log'
        );
    }
}
