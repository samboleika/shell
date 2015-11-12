<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->registerJs("  
 
    $('#checkbox').on('click', function(){
        $('#submit-button').removeAttr('disabled');
    })
    
    $('#essay-form').on('beforeSubmit', function(evt){
        if($(this).find('.has-error').length) {
            return false;
        }
        $('#add-essay-button').attr('disabled', 'disabled');
        var fd = new FormData(this);
        $.ajax({
          type: 'POST',
          data: fd,
          dataType: 'json',
          enctype: 'multipart/form-data',
          processData: false,
          contentType: false
        }).done(function( data ) {
            if(data.status && data.status == 'ok'){
                $('#result').html('Спасибо!<br/><p>Ваша заявка принята!</p><p>После прохождения модерации и в случае отбора вашей заявки для еженедельного голосования на сайте,<br/>мы свяжемся с Вами по телефону.</p>');
                $.fancybox({
                    'href' : '#result',
                    'beforeClose': function() { document.location.href='".yii\helpers\Url::to("/")."' },
                    'closeClick': true
                });
            }
            else{
                $('#result').html('<p class=\"alert text-center\">Произошла ошибка, Ваше эссе не добавлено!</p>');
                $.fancybox({'href' : '#result'});
                $('#add-essay-button').removeAttr('disabled');
            }
        });
        return false;
    })
", yii\web\View::POS_READY);


if($redirect == 'login'){
	$this->registerJs(" 
		window.digitalData.loginEvent = {
			loginEvent: 'login'
		};
		_satellite.track('loginEvent');
		window.digitalData.loginEvent = {};
	", yii\web\View::POS_READY);
}
elseif($redirect == 'register'){
	$this->registerJs(" 
		window.digitalData.registrationEvent = {
			registrationEvent: 'registration'
		};
		_satellite.track('registrationEvent');
		window.digitalData.registrationEvent = {};
	", yii\web\View::POS_READY);
}

$this->title = 'Форма регистрации';
?>
<div class="content-registration">
    <div class="img-wrapper">
        <h2>Подать заявку <br>на участие в акции</h2>
    </div>
    <div class="text-wrapper">
        <p><strong>
            У каждого из нас есть своя цель - тот главный пункт назначения, который вы стремитесь достичь, преодолевая сотни километров дорог.  
        </strong></p>
        <p>
            Расскажите нам о ваших главных целях и почему их достижение так важно для вас и ваших близких. Ведь вместе с Shell Rimula вы можете достичь любой цели!
        </p>
    </div>
     
    <?php $form = ActiveForm::begin([
        'id' => 'essay-form',
        'options' => ['enctype'=>'multipart/form-data', 'class' => 'registr'],
        'fieldConfig' => [
            'template' => "<div class=\"form-group\">{input}</div>"
        ]
    ]); ?>
    
    <?= $form->field($model, 'text', [
            'template' => '<div class="input-item">{input}</div>',
            'inputOptions' => ['placeholder' => 'Напишите краткое эссе на тему «На пути к цели с Shell Rimula» (не более 1000 символов)']
        ])->textarea() ?>

    <div class="input-item foto-esse input-file">
        <div class="fileform">
            <div class="selectbutton">Загрузить фото</div>
            <div id="fileformlabel"></div>
            <input type="file" name="EssayForm[photo]" id="upload" onchange="getName(this.value);" />
        </div>
        <label for="upload">
            (файл в формате .JPG, не более 3МБ) Принимаются фотографии
            самих участников  и  фотографии, связанные с тематикой акции.
        </label>
    </div>
    
    <div class="input-item checkbox">
        <input type="checkbox" name="agreement" id="checkbox" > <label for="checkbox">Я согласен с <a href="/files/shell_rimula_rules.pdf" target="_blank" download="shell_rimula_rules.pdf">Правилами акции</a></label>
    </div>		
    
    <div class="input-item">				
        <button class="submit" id="submit-button" disabled>Отправить заявку на рассмотрение жюри!</button>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<div class="hidden">
    <div id="result" class="my-fancy">
    </div>
</div>
