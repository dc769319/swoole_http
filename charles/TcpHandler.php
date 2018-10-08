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
            $this->send($server, $fd, json_encode(['code' => 13000, 'msg' => 'Illegal request']));
        }
        $requestData = json_decode($data, true);
        if (!isset($requestData['uri'])) {
            $this->send($server, $fd, json_encode(['code' => 13001, 'msg' => 'Invalid request uri']));
        }
        switch ($requestData['uri']) {
            case 'user':
                $uid = isset($requestData['uid']) ? intval($requestData['uid']) : 0;
                if (empty($uid)) {
                    $this->send($server, $fd, json_encode(['code' => 1, 'msg' => 'Empty uid', 'data' => []]));
                }
                $this->send(
                    $server,
                    $fd,
                    json_encode(['code' => 1, 'msg' => 'Success', 'data' => ['name' => 'charles', 'age' => 25]])
                );
                break;
            default:
                $this->send(
                    $server,
                    $fd,
                    json_encode(['code' => 13002, 'msg' => 'Unknown request uri'])
                );
                break;
        }
    }

    /**
     * 向客户端发送数据
     * @param TcpServer $server
     * @param int $fd
     * @param string $data
     * @return bool
     */
    private function send(TcpServer $server, int $fd, string $data)
    {
        return $server->send($fd, Protocol::encode($data));
    }
}
