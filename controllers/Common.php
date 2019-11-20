<?php
namespace bricksasp\worker\controllers;

use Yii;
use yii\web\Controller;
use GatewayClient\Gateway;

class Common extends Controller
{
    public function init()
    {
        Gateway::$registerAddress = Yii::$app->params['workerConfig']['registerAddress'];
    }
    public function success($data=[], $message='ok')
    {
        return json_encode(['controller' => $this->id, 'action' => $this->action->id, 'status'=>200, 'message'=>$message, 'data'=>$data],JSON_UNESCAPED_UNICODE);
    }

    public function fail($message='ok', $data=[])
    {
        return json_encode(['controller' => $this->id, 'action' => $this->action->id, 'status'=>400, 'message'=>$message, 'data'=>$data],JSON_UNESCAPED_UNICODE);
    }

    public function actionDefault(array $data)
    {
    	return $this->success($data);
    }
}
