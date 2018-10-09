<?php

namespace Charles;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpHandler
{
    /**
     * 处理客户端发送来的Http请求
     * @param Request $request
     * @param Response $response
     * @throws \Exception
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
                $recData = json_decode($res, true);
                if (empty($recData) || !isset($recData['code'])) {
                    $this->responseJson(['code' => 14002, 'msg' => 'Server error'], $response);
                }
                if ($recData['code'] != 1) {
                    //服务端进程返回错误信息，记录日志
                    $this->log(sprintf('return data: %s', $res), 'server error msg');
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
     * @throws \Exception
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
