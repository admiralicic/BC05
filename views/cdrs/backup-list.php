<?php


use yii\helpers\Html;
use yii\grid\GridView;


?>

<div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'filename',
            'size',
            'created',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{restore}',
                'buttons' => [
                    'restore' => function($url, $model, $key){
                        return Html::a('Restore', ['restore', 'id'=>$model['filename']]);
                    }
                ]
            ],
        ],
    ]); ?>
</div>
