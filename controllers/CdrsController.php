<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\CdrsModel;


class CdrsController extends Controller
{



    public function actionIndex()
    {
        $model = new CdrsModel();

        //return $this->render('index', ['model' => $model] );

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // valid data received in $model

            // do something meaningful here about $model ...

            return $this->render('index', ['model' => $model]);
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('index', ['model' => $model]);
        }


    }

}