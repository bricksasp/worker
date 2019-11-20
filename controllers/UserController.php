<?php
namespace bricksasp\worker\controllers;

use Yii;
use bricksasp\rbac\models\User;
use GatewayClient\Gateway;

class UserController extends Common
{

    /**
     * @OA\Get(path="/user/bind",
     *   summary="websocket 请求事例说明",
     *   tags={"worker模块"},
     *   description="绑定socket 请求实例{'controller':'user','action':'bind','params':{'token':'用户登录标示','client_id':'websocket链接成功后返回'}} 连接websocket 实例 var ws = new WebSocket('ws://127.0.0.1:8282?X-Token=token');",
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/socket"),
     *     ),
     *   ),
     * )
     *
     * 
     * @OA\Schema(
     *   schema="socket",
     *   description="响应结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="controller", type="string", description="socket 控制器指令"),
     *       @OA\Property(property="action", type="string", description="socket 动作指令"),
     *       @OA\Property(property="message", type="string", description="请求信息"),
     *       @OA\Property(property="status", type="integer", description="请求状态"),
     *       @OA\Property(property="data", type="array", description="请求参数", @OA\Items(ref="#/components/schemas/params")),
     *     )
     *   }
     * )
     */
    public function actionBind(string $token, string $client_id)
    {
        $user = User::findIdentityByAccessToken($token);
        if (empty($user)) {
        	return $this->fail(Yii::t('base',50001));
        }

		Gateway::unbindUid($client_id, $user->id);
        Gateway::bindUid($client_id,$user->id);
        return $this->success('','绑定成功');
    }

}
