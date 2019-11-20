<?php
use Workerman\Worker;
use GatewayWorker\Gateway;
use bricksasp\rbac\models\User;

// gateway 进程，这里使用Text协议，可以用telnet测试
$gateway = new Gateway($worker_config['gateway']['protocols']);
// gateway名称，status方便查看
$gateway->name = $worker_config['gateway']['name'];
// gateway进程数
$gateway->count = $worker_config['gateway']['count'];
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = $worker_config['gateway']['lanIp'];
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
$gateway->startPort = $worker_config['gateway']['startPort'];
// 服务注册地址
$gateway->registerAddress = $worker_config['registerAddress'];

// 心跳间隔
$gateway->pingInterval = $worker_config['gateway']['pingInterval'];
if ($worker_config['serverPing']) {
    $gateway->pingNotResponseLimit = 0;
    // 心跳数据
    $gateway->pingData = 'ping';
}else{
    $gateway->pingNotResponseLimit = 1;
    $gateway->pingData = '';
}


 
// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
$gateway->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
        if(!YII_DEBUG && !in_array($_SERVER['HTTP_ORIGIN'], []))
        {
            $connection->close();
        }
        if ($common = Yii::$app->request->get('common')) {
            if (!in_array($common, ['qrscan'])) {
                $connection->close();
            }
        }else{
            $user = User::findIdentityByAccessToken(Yii::$app->request->get('X-Token'));
            if (empty($user)) {
                $connection->close();
            }
        }
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
    };
}; 


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

