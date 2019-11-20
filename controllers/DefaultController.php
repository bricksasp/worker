<?php
namespace bricksasp\worker\controllers;

use Yii;

/**
 * Default controller for the `worker` module
 */
class DefaultController extends Common
{
	public function actions() {
		return [
			'error' => [
				'class' => \bricksasp\base\actions\ErrorAction::className(),
			],
			'api-docs' => [
				'class' => 'genxoft\swagger\ViewAction',
				'apiJsonUrl' => \yii\helpers\Url::to(['api-json'], true),
			],
			'api-json' => [
				'class' => 'genxoft\swagger\JsonAction',
				'dirs' => [
                    Yii::getAlias('@bricksasp/worker'),
					dirname(__DIR__)
				],
			],
		];
	}

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

}
