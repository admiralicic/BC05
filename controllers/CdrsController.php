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

            return $this->redirect(Yii::$app->urlManager->createUrl("cdrs/backup-list"));
        } else {

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

        return $this->render('restored', ['model' => substr($id, 0, strlen($id) - 4)]);
    }

    public function actionRestored($id){
        $model = new CdrsModel();

        $list = $model->readRestored($id);

        $provider = new ArrayDataProvider([
            'allModels' => $list,
            'sort'=> [
                'attributes' => ['site_id', 'started_at'],
            ],
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        return $this->render('restored-list', ['dataProvider' => $provider]);
    }

}