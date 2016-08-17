<?php

use yii\helpers\Html;

?>
<div>
    <h3>Backup restored</h3>
    <p>You can browse the data at the following
        <?= Html::a('link', ['cdrs/restored', 'id'=>$model]); ?>
</div>