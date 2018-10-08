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
            $response->end('Request error');
        }
        $uri = trim($request->server['request_uri']);
        switch ($uri) {
            case '/user':
                $id = isset($request->post['id']) ? $request->post['id'] : '';
                if (empty($id)) {
                    $response->end(json_encode(['code' => 14000, 'msg' => 'Invalid param id']));
                }
                $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
                $client->set([
                    'open_eof_check' => true,
                    'package_eof' => PACKAGE_EOF
                ]);
                if (!$client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT, 10)) {
                    $errCode = $client->errCode;
                    $errMsg = socket_strerror($errCode);
                    $this->log(
                        sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                        'TCP Server connect error'
                    );
                    $response->end(json_encode(['code' => 15000, 'msg' => 'Server error']));
                }
                $package = Protocol::encode(json_encode(['uri' => 'user', 'uid' => $id]));
                if (!$client->send($package)) {
                    $errCode = $client->errCode;
                    $errMsg = socket_strerror($errCode);
                    $this->log(
                        sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                        'Package send error'
                    );
                    $response->end(json_encode(['code' => 15001, 'msg' => 'Server error']));
                }
                $result = $client->recv();
                $this->log(print_r($result, true), 'receive data');
                if (false === $result) {
                    $errCode = $client->errCode;
                    $errMsg = socket_strerror($errCode);
                    $this->log(
                        sprintf("errCode:%d, errMsg:%s", $errCode, $errMsg),
                        'Package receive error'
                    );
                    $response->end(json_encode(['code' => 15002, 'msg' => 'Server error']));
                }
                //关闭tcp连接
                $client->close();
                //解包
                $res = Protocol::decode($result);
                $recData = json_decode($res, true);
                if (empty($recData)) {
                    $response->end(json_encode(['code' => 15003, 'msg' => 'Server error']));
                }
                $response->end(json_encode($recData));
                break;
            default:
                $response->end(sprintf('Your request uri is "%s"%s', $uri, PHP_EOL));
                break;
        }
    }

    /**
     * 记录日志
     * @param string $message
     * @param string $title
     * @throws \Exception
     */
    private function log($message, $title)
    {
        Log::add(
            $message,
            $title,
            'logs/http_handler.log'
        );
    }
}
