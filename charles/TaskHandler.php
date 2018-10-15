<?php

namespace Charles;

/**
 * Task进程任务处理器
 * Class TaskHandler
 * @package Charles
 * @author dongchao
 * @email dongchao@bigo.sg
 */
class TaskHandler
{
    /**
     * 处理worker进程投递的任务日
     * @param TcpServer $server
     * @param int $taskId
     * @param int $fromId
     * @param string $data
     */
    public static function task(TcpServer $server, int $taskId, int $fromId, string $data)
    {
        Log::add(
            sprintf('taskId:%d, fromId:%d, data:%s', $taskId, $fromId, $data),
            'task start',
            'task_handler'
        );
        $taskData = json_decode($data, true);
        if (!isset($taskData['uri'])) {
            //非法数据
            $server->finish(json_encode(['code' => 13001, 'msg' => 'Invalid uri', 'data' => []]));
        }
        //模拟耗时任务
        sleep(30);
        Log::add(sprintf('taskId:%d, fromId:%d', $taskId, $fromId), 'task end', 'task_handler');
        $server->finish(json_encode(['code' => 1, 'msg' => 'Success', 'data' => []]));
    }

    /**
     * 任务完成后的回调方法
     * @param TcpServer $server
     * @param int $taskId
     * @param string $data
     */
    public static function finish(TcpServer $server, int $taskId, string $data)
    {
        Log::add(
            sprintf('taskId:%d, received:%s', $taskId, $data),
            'task finished',
            'task_handler'
        );
    }
}
