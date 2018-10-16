<?php
$process = new \Swoole\Process('childProcessCreated');
$process->name('charles_process');
$process->start();
function childProcessCreated(\Swoole\Process $process)
{
    $process->name('charles_process_child');
    while (true) {
        echo 'Hello' . PHP_EOL;
        sleep(5);
    }
}
\Swoole\Process::wait();
