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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $model->backup();

            return $this->render('index', ['model' => $model]);
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('index', ['model' => $model]);
        }


    }

}