<?php
namespace bricksasp\worker\controllers;

class IndexController extends Common
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
