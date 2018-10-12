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
    public $handler = null;

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
     */
    public function onWorkerStart(HttpServer $server, int $workerId)
    {
        swoole_set_process_name('charles_http_worker');
        $this->handler = new HttpHandler();
    }

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @throws \Exception
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->handler->handle($request, $response);
    }

    /**
     * @param HttpServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose(HttpServer $server, int $fd, int $reactorId)
    {
        $this->log(
            sprintf('fid:%d, reactorId:%d', $fd, $reactorId),
            'Http server closed'
        );
    }

    /**
     * @param HttpServer $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError(
        HttpServer $server,
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
     */
    private function log(string $message, string $title)
    {
        Log::add(
            $message,
            $title,
            'http_server.log'
        );
    }
}
