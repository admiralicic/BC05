<?php

use yii\helpers\Html;
use yii\grid\GridView;

?>

<div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'site_id',
            'started_at',
            'duration',
        ],
    ]); ?>
</div>


