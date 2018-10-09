<?php

namespace Charles;

/**
 * Class TcpServer
 */
class TcpHandler
{
    /**
     * 处理tcp请求
     * @param TcpServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     * @throws \Exception
     */
    public function handle(TcpServer $server, string $data, int $fd, int $reactorId)
    {
        $data = Protocol::decode($data);
        if (empty($data)) {
            $this->response($server, $fd, ['code' => 13000, 'msg' => 'Illegal request']);
        }
        $requestData = json_decode($data, true);
        if (!isset($requestData['uri'])) {
            $this->response($server, $fd, ['code' => 13001, 'msg' => 'Invalid request uri']);
        }

        switch ($requestData['uri']) {
            case 'user':
                $uid = $requestData['uid'] ?? 0;
                if (empty($uid)) {
                    $this->response($server, $fd, ['code' => 13002, 'msg' => 'Empty uid', 'data' => []]);
                }
                $this->response($server, $fd, [
                    'code' => 1,
                    'msg' => 'Success',
                    'data' => ['name' => 'charles', 'age' => 25]
                ]);
                break;
            default:
                $this->response($server, $fd, [
                    'code' => 1,
                    'msg' => 'Success',
                    'data' => ['code' => 13002, 'msg' => 'Unknown request uri']
                ]);
                break;
        }
    }

    /**
     * 向客户端发送响应数据
     * @param TcpServer $server
     * @param int $fd
     * @param array $data
     * @return bool
     */
    private function response(TcpServer $server, int $fd, array $data)
    {
        $response = Protocol::encode(json_encode($data));
        return $server->send($fd, $response);
    }
}
