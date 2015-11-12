<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Обратная связь';
?>
<div class="content-callback">

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>

        <div class="alert alert-success">
            Спасибо! Ваше обращение будет обработано!
        </div>

    <?php else: ?>

    <div class="text-wrapper">
        <h1>Форма обратной связи</h1>
        <p>
            Данная форма предназначена для отправки любых вопросов об участии в программе, 
            а также своих пожеланий и рекомендаций представителям Организатора акции.
        </p>
        <p>Будем рады Вам помочь!</p>
    </div>
        
    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

        <?= $form->field($model, 'name', [
            'template' => '<div class="input-item">{input}</div>',
            'inputOptions' => ['placeholder' => 'Ваше имя', 'class' => 'input-width-368']
        ])->label(false); ?>
        
        <?= $form->field($model, 'email', [
            'template' => '<div class="input-item">{input}</div>',
            'inputOptions' => ['placeholder' => 'Ваш e-mail', 'class' => 'input-width-368']
        ])->label(false); ?> 

        <?= $form->field($model, 'subject', [
            'template' => '<div class="input-item">{input}</div>',
            'inputOptions' => ['placeholder' => 'Тема обращения']
        ])->label(false); ?>

        <?= $form->field($model, 'body', [
            'template' => '<div class="input-item">{input}</div>',
            'inputOptions' => ['placeholder' => 'Текст обращения (не более 400 символов)']
        ])->textArea(['rows' => 6])->label(false); ?>

        <div class="input-item">
            <?= Html::submitButton('Отправить', ['class' => 'submit', 'name' => 'contact-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <?php endif; ?>
</div>
