<?php 
use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use bricksasp\worker\Events;

// bussinessWorker 进程
$worker = new BusinessWorker($worker_config['businessWorker']['protocols']);
// worker名称
$worker->name = $worker_config['businessWorker']['name'];
// bussinessWorker进程数量
$worker->count = $worker_config['businessWorker']['count'];
// 服务注册地址
$worker->registerAddress = $worker_config['registerAddress'];
$worker->eventHandler = Events::className();

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

