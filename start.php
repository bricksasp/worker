<?php
ini_set('display_errors', 'on');
use Workerman\Worker;

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require_once __DIR__ . '/../../basp-saas/vendor/autoload.php';
require_once __DIR__ . '/../../basp-saas/vendor/yiisoft/yii2/Yii.php';
require_once __DIR__ . '/../../basp-saas/config/bootstrap.php';

$yii_config = require_once __DIR__ . '/../../basp-saas/config/web.php';

(new yii\web\Application($yii_config))->init();

$worker_config = array_merge([
	'registerAddress' => '127.0.0.1:1238',
	'serverPing' => false,
	'businessWorker'  => [
		//进程名称
		'name' => 'App_Business',
		//协议
		'protocols' => null,
		//进程数
		'count' => 4,
	],
	'gateway'  => [
		'name' => 'App_Gateway',
		'protocols' => 'websocket://0.0.0.0:8282',//外部访问端口
		'count' => 4,
		// 本机ip，分布式部署时使用内网ip
		'lanIp' => '127.0.0.1',
		'startPort' => 2900,
		'pingInterval' => 50,
	],
], Yii::$app->params['workerConfig'] ?? []);

Worker::$stdoutFile = './runtime/debug.log';
Worker::$pidFile = './runtime/workerman.pid';
Worker::$logFile = './runtime/workerman.log';

// 加载所有web/*/start.php，以便启动所有服务
foreach(glob(__DIR__.'/w*.php') as $start_file)
{
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();


/**
 * web前段测试代码
 var ws = new WebSocket("ws://127.0.0.1:8282");
    ws.onopen = function(){
		console.info("与服务端连接成功");
		ws.send('test msg\n');//相当于发送一个初始化信息
		console.info("向服务端发送心跳包字符串");
		setInterval(show,3000);
		}
	
	function show(){
		ws.send('ping\n');
		}	
  
  	ws.onConnect = function(e){

		}
	ws.onmessage = function(e){
console.log(e.data);
		}
//心跳处理
//获取会员id
ws.onclose = function(e){
	 console.log(e);
	}
 */
