<?php

use yii\grid\GridView;

?>

<div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,

    ]); ?>
</div>


