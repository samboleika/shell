<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->registerJs("
    $('#agreement_check').on('click', function(){
        $('#signup-button').removeAttr('disabled');
    })
", yii\web\View::POS_READY);

$this->title = 'Участовать';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div>
        <h2>Для продолжения авторизуйся</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form', 
        ]); ?>

            <?= $form->field($loginForm, 'username', ['inputOptions' => ['placeholder' => '+7 (___) ___-__-__']])->label(false); ?>
            <?= $form->field($loginForm, 'password', ['inputOptions' => ['placeholder' => '******']])->passwordInput()->label(false); ?>

            <div class="form-group">
                <?= Html::submitButton('Ок', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>

    </div>
    <div>
        <h2>Или зарегистрируйся</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'signup-form',
            'fieldConfig' => [
                'template' => "<div class=\"form-group\">{input}</div>"
            ],
        ]); ?>

            <?= $form->field($signupForm, 'lastname', ['inputOptions' => ['placeholder' => 'Фамилия']])->label(false); ?>
            <?= $form->field($signupForm, 'firstname', ['inputOptions' => ['placeholder' => 'Имя']])->label(false); ?>
            <?= $form->field($signupForm,'birth_date')->widget(yii\jui\DatePicker::className(),[
                'language' => 'ru',
                'dateFormat' => 'dd.MM.yyyy',
                'options' => [ 
                    'placeholder' => 'дд.мм.гггг', 
                    'readonly' => 'readonly', 
                    'class' => 'form-control'
                ],
                'clientOptions' => [
                    'changeMonth' => true,
                    'changeYear' => true,
                    'yearRange' => "-100:+0"
                ],
            ]) ?>
            <?= $form->field($signupForm, 'city', ['inputOptions' => ['placeholder' => 'Город']])->label(false); ?>
           <?= $form->field($signupForm, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '+7 (999) 999-99-99',
                'options' => ['placeholder' => '+7 (___) ___-__-__'],
            ]) ?>
            <div class="row">
                <?= Html::submitButton('Подтвердить', ['class' => 'btn', 'id' => 'confirm_phone']) ?>
            </div>
            <br/>
            <?= $form->field($signupForm, 'password', ['inputOptions' => ['placeholder' => 'Введите пароль']])->passwordInput()->label(false); ?>
            <?= $form->field($signupForm, 'password_repeat', ['inputOptions' => ['placeholder' => 'Подтвердите пароль']])->passwordInput()->label(false); ?>
            
            <?=Html::checkbox("agreement", false, ['id'=>'agreement_check']) . " <span>Я согласен с правилами акции</span>";?>
            <div class="form-group">
                <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary', 'name' => 'signup-button', 'id' => 'signup-button', 'disabled' => 'disabled']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
