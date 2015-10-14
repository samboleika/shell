<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Административный интерфейс';

?>
<div class="content-registration text-center">
    <h1><?=$this->title?></h1>
    <div style="width: 300px;display: inline-block;">
        <?php $form = ActiveForm::begin([
            'id' => 'login-form'
        ]); ?>

            <h3>Авторизоваться</h3>
            <?= $form->field($loginForm, 'username')->label(false); ?>
            <?= $form->field($loginForm, 'password')->passwordInput()->label(false); ?>

            <?= Html::submitButton('Вход', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>

        <?php ActiveForm::end(); ?>

    </div>
