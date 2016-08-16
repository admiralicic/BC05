<?php


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\DatePicker;

$this->title = 'Cdrs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>
        <?= $form->field($model, 'start_date')->widget(DatePicker::classname(), [
            'language' => 'en',
            'dateFormat' => 'dd-MM-yyyy',
            'options' => ['class' => 'form-control'],
        ]); ?>
        <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
            'language' => 'en',
            'dateFormat' => 'dd-MM-yyyy',
            'options' => ['class' => 'form-control'],
        ]); ?>
        <?= $form->field($model, 'to_delete')->checkbox([
                'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
        ]);

        ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Backup', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php ActiveForm::end() ?>

</div>