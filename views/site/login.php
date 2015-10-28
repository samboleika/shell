<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\yii\web\View::registerJsFile('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places');  

$this->registerJs("
    $('#agreement_check').on('click', function(){
        if(!$('#confirmed_phone').hasClass('hidden')){
            $('#signup-button').removeAttr('disabled');
        }
    })
    
    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('signupform-city'), {
        language: 'ru',
        componentRestrictions: {country: 'ru'},
        types: ['(cities)']
    });
	
    autocomplete.addListener('place_changed', function(){
        var place = autocomplete.getPlace();
        $('#signupform-city').val(place.name);
    });
    
	$('#confirm_phone').on('click', function(){
        var phone_number = $('#signupform-phone').val();
        if(phone_number.replace( /[\+\-\_\(\) ]/g, '').length == 11){
            $('#phone_code').val('');
            $('#modal_phone_error').addClass('hidden');
            $.ajax({
                data:'sendCodePhone=' + $('#signupform-phone').val(),
                dataType: 'json',
                success: function(data){
                    $('#modal_phone_error').html(data.text);
                    $.fancybox({'href' : '#phone-modal'});
                },
                error: function(){
                    $('#modal_phone_error').html('Ошибка: код не отправлен!');
                    $.fancybox({'href' : '#phone-modal'});
                }
            })
        }
	})
    
	$('#modal_send_code').on('click', function(){
			$.ajax({
				data:'checkCode=' + $('#phone_code').val() + '&phone=' + $('#signupform-phone').val(),
				dataType: 'json',
				success: function(data){
					if(data.status == 'ok'){
						$('#confirm_phone').addClass('hidden');
						$('#confirmed_phone').removeClass('hidden');
						$('#signupform-phone').attr('readonly', 'readonly');
						$.fancybox.close();
                        
                        if($('#agreement_check').is(':checked')){
                            $('#signup-button').removeAttr('disabled');
                        }
					}
					else{
						$('#modal_phone_error').removeClass('hidden').html(data.text);
					}
				},
				error: function(){
                    $('#modal_phone_error').removeClass('hidden').html('Ошибка: попробуйте позже!');
				}
			})
	})
	
", yii\web\View::POS_READY);

//$this->title = 'Участовать';

?>
<div class="content-registration">
    <div class="text-wrapper">
        <h2>Форма регистрации для участия в акции
        «На пути к цели с Shell Rimula»</h2>
        <p>
            Уважаемый Участник,
        </p>
        <p>
            Пройдите регистрацию в программе, заполнив соответствующие поля, или  продолжите авторизацию, если Вы уже являетесь зарегистрированным пользователем. 
        </p>
        <p>
            Желаем удачи! 
        </p>
    </div>
    <div>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form', 
            'options' => ['class' => 'autoriz'],
            'fieldConfig' => [
                'template' => "<div class=\"input-item\">{input}</div>"
            ]
        ]); ?>

            <h3>Авторизоваться</h3>			
            <?= $form->field($loginForm, 'username')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '+7 (999) 999-99-99',
                'options' => ['placeholder' => '+7 (___) ___-__-__', 'class' => 'input-width-368'],
            ]) ?>
            <?= $form->field($loginForm, 'password', ['inputOptions' => ['placeholder' => 'Пароль', 'class' => 'input-width-368']])->passwordInput()->label(false); ?>

            <?= Html::submitButton('Вход', ['class' => 'input-width-368 submit', 'name' => 'login-button']) ?>

        <?php ActiveForm::end(); ?>

    </div>
    <div>

        <?php $form = ActiveForm::begin([
            'id' => 'signup-form',
            'options' => ['class' => 'registr'],
            'fieldConfig' => [
                'template' => "<div class=\"input-item\">{input}</div>"
            ]
        ]); ?>

            <h3>Зарегистрироваться</h3>
        
            <?= $form->field($signupForm, 'lastname', ['inputOptions' => ['placeholder' => 'Фамилия', 'class' => 'input-width-368']])->label(false); ?>
            <?= $form->field($signupForm, 'firstname', ['inputOptions' => ['placeholder' => 'Имя', 'class' => 'input-width-368']])->label(false); ?>
            <?= $form->field($signupForm,'birth_date')->widget(yii\jui\DatePicker::className(),[
                'language' => 'ru',
                'dateFormat' => 'dd.MM.yyyy',
                'options' => [ 
                    'placeholder' => 'дд.мм.гггг', 
                    'readonly' => 'readonly', 
                    'class' => 'input-width-368'
                ],
                'clientOptions' => [
                    'changeMonth' => true,
                    'changeYear' => true,
                    'yearRange' => "-100:+0"
                ],
            ]) ?>
            <?= $form->field($signupForm, 'city', ['inputOptions' => ['placeholder' => 'Город', 'class' => 'input-width-368']])->label(false); ?>
			<?= $form->field($signupForm, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '+7 (999) 999-99-99',
                'options' => ['placeholder' => '+7 (___) ___-__-__', 'class' => 'input-width-368'],
            ]) ?>
            <?= Html::button('Подтвердить', ['class' => 'input-width-368 submit', 'id' => 'confirm_phone']) ?>
            <p class="text-success hidden" id="confirmed_phone">Подтвержден</p>
            <br/>
            <?= $form->field($signupForm, 'password', ['inputOptions' => ['placeholder' => 'Введите пароль', 'class' => 'input-width-368']])->passwordInput()->label(false); ?>
            <?= $form->field($signupForm, 'password_repeat', ['inputOptions' => ['placeholder' => 'Подтвердите пароль', 'class' => 'input-width-368']])->passwordInput()->label(false); ?>
            <div class="input-item checkbox">
                <input type="checkbox" name="agreement" id="agreement_check"> <label for="checkbox">Я согласен с <a href="/files/shell_rimula_rules.pdf" target="_blank">Правилами акции</a></label>
            </div>
        
            <?= Html::submitButton('Зарегистрироваться', ['class' => 'input-width-368 submit', 'name' => 'signup-button', 'id' => 'signup-button', 'disabled' => 'disabled']) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

    <!-- phone-modal -->
<div class="hidden">
    <div id="phone-modal" class="my-fancy">
        <p>Введите код из СМС, отправленного на указанный номер телефона</p> 
        <br/>
        <div class="input-item"><input type="text" id="phone_code" placeholder="******" class="input-width-368"></div>
        <p class="hidden" id="modal_phone_error"></p>
        <div class="inline-center"><button class="input-width-368 submit" id="modal_send_code">ОК</button></div>
    </div>
</div>
