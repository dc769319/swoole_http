<?php

namespace Charles;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpHandler
{
    public $workerId = null;

    public function __construct(int $workerId)
    {
        $this->workerId = $workerId;
    }

    /**
     * 处理客户端发送来的Http请求
     * @param Request $request
     * @param Response $response
     */
    public function handle(Request $request, Response $response)
    {
        if (!isset($request->server['request_uri'])) {
            $this->responseRaw('Request error', $response);
        }
        $uri = trim($request->server['request_uri']);
        switch ($uri) {
            case '/user':
                $id = $request->post['id'] ?? '';
                if (empty($id)) {
                    $this->responseJson(['code' => 14000, 'msg' => 'Invalid param id'], $response);
                }
                $res = SyncTcpClient::send(json_encode(['uri' => 'user', 'uid' => $id]));
                if (false === $res) {
                    $this->responseJson(['code' => 14001, 'msg' => 'Server error'], $response);
                }
                //服务端进程返回错误信息，记录日志
                $this->log(sprintf('return data: %s', $res), 'received data');
                $recData = json_decode($res, true);
                if (empty($recData) || !isset($recData['code'])) {
                    $this->responseJson(['code' => 14002, 'msg' => 'Server error'], $response);
                }
                if ($recData['code'] != 1) {
                    $this->responseJson(['code' => 14003, 'msg' => 'Server error'], $response);
                }
                $this->responseJson([
                    'code' => 1,
                    'msg' => 'Success',
                    'data' => $recData['data'] ?? []
                ], $response);

                break;
            case '/video':
                $this->responseJson([
                    'code' => 1,
                    'msg' => 'Success',
                    'data' => ['vid' => 1, 'src' => 'https://www.youtube.com/watch?v=5EcPasxxFP4']
                ], $response);
                break;
            case '/profile':
                $id = $request->post['id'] ?? '';
                if (empty($id)) {
                    $this->responseJson(['code' => 14000, 'msg' => 'Invalid param id'], $response);
                }
                //发送异步非阻塞请求
                AsyncTcpClient::send(
                    json_encode(['uri' => 'profile', 'uid' => $id]),
                    function ($unpackedData) use ($response) {
                        //接收到服务端数据后执行的回调函数
                        $recData = json_decode($unpackedData, true);
                        if (empty($recData) || !isset($recData['code'])) {
                            $this->responseJson(['code' => 14002, 'msg' => 'Server error'], $response);
                        }
                        if ($recData['code'] != 1) {
                            $this->responseJson(['code' => 14003, 'msg' => 'Server error'], $response);
                        }
                        $this->responseJson([
                            'code' => 1,
                            'msg' => 'Success',
                            'data' => $recData['data'] ?? []
                        ], $response);
                    }
                );
                break;
            case '/push':
                $pushId = $request->post['pushId'] ?? '';
                if (empty($pushId)) {
                    $this->responseJson(['code' => 14000, 'msg' => 'Invalid param pushId'], $response);
                }
                //发送异步非阻塞请求
                AsyncTcpClient::send(
                    json_encode(['uri' => 'push', 'pushId' => $pushId]),
                    function ($unpackedData) use ($response) {
                        //接收到服务端数据后执行的回调函数
                        $recData = json_decode($unpackedData, true);
                        if (empty($recData) || !isset($recData['code'])) {
                            $this->responseJson(['code' => 14002, 'msg' => 'Server error'], $response);
                        }
                        if ($recData['code'] != 1) {
                            $this->responseJson(['code' => 14003, 'msg' => 'Server error'], $response);
                        }
                        $this->responseJson([
                            'code' => 1,
                            'msg' => 'Success',
                            'data' => $recData['data'] ?? []
                        ], $response);
                    }
                );
                break;
            default:
                $this->responseRaw(sprintf('Your request uri is "%s"', $uri), $response);
                break;
        }
    }

    /**
     *
     * @param string $message
     * @param Response $response
     */
    private function responseRaw(string $message, Response $response)
    {
        $response->header("Content-Type", "text/plain; charset=utf-8");
        $response->end($message . PHP_EOL);
    }

    /**
     * json响应
     * @param array $data
     * @param Response $response
     */
    private function responseJson(array $data, Response $response)
    {
        $response->header("Content-Type", "application/json; charset=utf-8");
        $response->end(json_encode($data) . PHP_EOL);
    }

    /**
     * 记录日志
     * @param string $message
     * @param string $title
     */
    private function log(string $message, string $title)
    {
        Log::add(
            $message,
            $title,
            'http_handler'
        );
    }
}
