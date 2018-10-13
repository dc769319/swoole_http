<?php

namespace Charles;

/**
 * Class TcpServer
 */
class TcpHandler
{
    public $workerId = null;

    public function __construct(int $workerId)
    {
        $this->workerId = $workerId;
    }

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
        Log::add(
            sprintf("workerId:%d, data:%s", $this->workerId, $data),
            'receive data',
            'tcp_handler'
        );
        $data = TextProtocol::decode($data, $seqNo);
        Log::add(
            sprintf("workerId:%d, data:%s", $this->workerId, $data),
            'unpacked data',
            'tcp_handler'
        );
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
            case 'profile':
                $uid = $requestData['uid'] ?? 0;
                if (empty($uid)) {
                    $this->response($server, $fd, ['code' => 13003, 'msg' => 'Empty uid', 'data' => []]);
                }
                $this->response($server, $fd, [
                    'code' => 1,
                    'msg' => 'Success',
                    'data' => ['uid' => $uid, 'name' => 'charles', 'age' => 25, 'contractData' => '2018/10/13']
                ], $seqNo);
                break;
            case 'PING':
                $this->response($server, $fd, [
                    'msg' => 'PONG'
                ], $seqNo);
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
     * @param int $reqNo 请求标识
     * @return bool
     */
    private function response(TcpServer $server, int $fd, array $data, int $reqNo = null)
    {
        $response = TextProtocol::encode(json_encode($data), $reqNo);
        Log::add(
            sprintf("workerId:%d, data:%s", $this->workerId, $response),
            'send data',
            'tcp_handler'
        );
        return $server->send($fd, $response);
    }
}
