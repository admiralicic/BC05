<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\CdrsModel;
use yii\data\SqlDataProvider;
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

        $table_name = 'temp_cdrs_'.$id;

        $count = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM $table_name")->queryScalar();

        $provider = new SqlDataProvider([
            'sql' => "SELECT * FROM $table_name ORDER BY started_at",
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'site_id',
                    'started_at',
                ],
            ],
        ]);

        return $this->render('restored-list', ['dataProvider' => $provider]);
    }

}