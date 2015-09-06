<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->registerJs(" 
    
    $('#agreement_check').on('click', function(){
        $('#add-essay-button').removeAttr('disabled');
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
                $('#essay-modal .modal-body').html('<div class=\"text-center\"><b>Спасибо</b><br/><p>Твоя работа принята на модерацию.</p><p>По итогам модерации мы вылем письмо на указанный</p><p>тобой адрес E-mail. Если твоя работа станет выигрышной,<p>Менеджеры свяжутся с тобой.</p></div>');
                $('#essay-modal').modal('show');
                $('#essay-modal').on('hidden.bs.modal', function (e) {
                    document.location.href='".yii\helpers\Url::to("/")."';
                })
            }
            else{
                $('#essay-modal .modal-body').html('<p class=\"alert text-center\">Произошла ошибка, Ваше ессэ не добавлено!</p>');
                $('#essay-modal').modal('show');
                $('#add-essay-button').removeAttr('disabled');
            }
        });
        return false;
    })
", yii\web\View::POS_READY);

$this->title = 'Добавление работы';
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
       К участию в конкурсе приглашаются лица, 
       купившие продукцию Шелл Римула R5, R6 в фасовке 20 л в период проведения акции (с 1 октября по 31 ноября 2015 г.) 
       В качестве подтверждения покупки претенденты на победу должны предъявить чек с полным наименованием продукции (фото или скан чека).
    </p>

    <?php $form = ActiveForm::begin([
        'id' => 'essay-form',
        'options' => ['enctype'=>'multipart/form-data'],
        'fieldConfig' => [
            'template' => "<div class=\"form-group\">{input}</div>"
        ]
    ]); ?>
    
    <?= $form->field($model, 'text', ['inputOptions' => ['placeholder' => 'Напишите эссе на тему "Заветная мечта" (не более 1000 символов)']])->textarea() ?>
    <?= $form->field($model, 'photo', [
            'template' => '<div class="form-group">{input} <span>(файл в формате .JPG|.PNG|.JPEG, не более 3МБ)</span></div>',
            'inputOptions' => [
                'name' => 'photo',
                'class' => ''
            ]
        ])->fileInput() ?>
    
    <?=Html::checkbox("agreement", false, ['id'=>'agreement_check']) . " <span>Я согласен с правилами акции</span>";?>
    <div class="form-group">
        <?= Html::submitButton('Отправить на модерацию', ['class' => 'btn btn-primary', 'name' => 'add-essay-button', 'id' => 'add-essay-button', 'disabled' => 'disabled']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    
    <div class="modal fade" id="essay-modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <div class="text-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
            </div>
          </div>
        </div>
      </div>
    </div>

</div>
