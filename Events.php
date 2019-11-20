<?php
namespace bricksasp\worker;
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use GatewayWorker\Lib\Gateway;
use Yii;
use yii\base\BaseObject;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events extends BaseObject {
	/**
	 * 当客户端连接时触发
	 * 如果业务不需此回调可以删除onConnect
	 *
	 * @param int $client_id 连接id
	 */
	public static function onConnect($client_id) {
		Gateway::sendToClient($client_id, Yii::$app->runAction('worker/default/default', ['data' => ['client_id' => $client_id]]));
	}

	/**
	 * 当客户端发来消息时触发
	 * @param int $client_id 连接id
	 * @param mixed $message 具体消息
		// 向所有人发送
		// Gateway::sendToAll(Yii::$app->runAction($route,$params));
	 */
	public static function onMessage($client_id, $message) {
		if ($message == 'ping') {
			return;
		}
		//['controller'=>'default','action'=>'index',params=>[]]
		list($moduleId, $controllerId, $actionId, $params) = self::checkRoute($message);
		
		$route = implode('/', [$moduleId, $controllerId, $actionId]);
		Gateway::sendToClient($client_id, Yii::$app->runAction($route, $params));
	}

	/**
	 * 当用户断开连接时触发
	 * @param int $client_id 连接id
	 */
	public static function onClose($client_id) {
	}

	/**
	 * 检查路由是否存在
	 */
	public static function checkRoute($message) {
		$operation = json_decode($message, true);
		$moduleId = 'worker';

		$default = [$moduleId, 'default', 'default', [
			'data' => $message,
		]];
		if (!is_array($operation)) {
			return $default;
		}

		$controllerId = $operation['controller'];
		$actionId 	  = $operation['action'];

		$module = Yii::$app->getModule($moduleId);
		$controller = $module->createControllerByID($controllerId);
		if (empty($controller)) {
			return $default;
		}

		if (!$controller->hasMethod('Action' . ucfirst($actionId))) {
			return $default;
		}
		return [$moduleId, $controllerId, $actionId, $operation['params']];
	}
}
