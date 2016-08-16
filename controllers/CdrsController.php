<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\CdrsModel;
use yii\data\ArrayDataProvider;


class CdrsController extends Controller
{

    public function actionIndex()
    {
        $model = new CdrsModel();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $model->backup();

            //return $this->render('index', ['model' => $model]);
            return $this->redirect(Yii::$app->urlManager->createUrl("cdrs/backup-list"));
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('index', ['model' => $model]);


        }

    }

    public function actionBackupList(){
        $model = new CdrsModel();
        $provider = new ArrayDataProvider([
           'allModels' => $model->readFiles(),
            'sort' => [
                'attributes' => ['filename', 'size', 'created'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ]
        ]);
        return $this->render('backup-list', ['dataProvider' => $provider]);
    }

    public function actionRestore($id){
        $model = new CdrsModel();

        $model->restoreBackup($id);
    }
}