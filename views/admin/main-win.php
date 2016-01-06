<?php
use app\models\Essays;
use app\models\Weeks;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->registerJs('
    function getName (that){
        var str = that.value;
        if (str.lastIndexOf("\\\")){
            var i = str.lastIndexOf("\\\")+1;
        }
        else{
            var i = str.lastIndexOf("/")+1;
        }
        $(that).closest(".essay-form").submit(); 
    }
', yii\web\View::POS_HEAD);

$this->registerJs('
    $(".set_winner").on("click", function(){
        var _this = $(this);
        var essay_id = $(this).closest("tr").attr("data-essay-id");
        $.ajax({
            dataType: "json",
            data: "setWinner=1&essay_id=" + essay_id,
            success: function(data){
                if(!(data.status && data.status == "ok")){
                    if(data.text){
                        alert(data.text);
                        return false;
                    }
                    alert("Не получилось изменить статус");
                    return false;
                }
                else{
                    _this.closest("tr").find(".td_status").html("Победитель");
                    _this.closest("tr").find(".td_winner").hide();
                    return true;
                }
            },
            error: function(data){
                alert("Не получилось изменить статус");
                return false;
            }
        })
		return false;
    })
    
    $("#adminCarousel").carousel("pause");
    
    $(".open_essay").on("click", function(){
        var essay_id = $(this).closest("tr").attr("data-essay-id");
        $("#adminCarousel .item").removeClass("active");
        $("#essay_"+essay_id).addClass("active");
        $("#carousel-modal").modal("show");
        return false;
    })
    
    $("#filter-form").on("change", function(){
        this.submit();
    })
    
    $("#check_all").on("click", function(){
        if($(this).prop("checked")){
            $(".essay_check").attr("checked", "checked");
        }
        else{
            $(".essay_check").removeAttr("checked");
        }
    })
    
    $("#export_essays").on("click", function(){
        $("#essays-form").submit();
    });
    
    $(".essay-form").on("beforeSubmit", function(evt){
        var that = this;
        if($(this).find(".has-error").length) {
            return false;
        }
        var fd = new FormData(this);
        $.ajax({
          url: $(that).attr("action"),
          type: "POST",
          data: fd,
          dataType: "json",
          enctype: "multipart/form-data",
          processData: false,
          contentType: false
        }).done(function( data ) {
            if(data.status && data.status == "ok"){
                $(that).closest(".item").find(".essay-img-block").html("<img src=\'"+data.imgSrc+"\'>");
            }
            else if(data.text){
                alert(data.text);
            }
            else{
                alert("Произошла ошибка, фото не добавлено!");
            }
        });
        return false;
    })
', yii\web\View::POS_READY);

$this->title = 'Главный розыгрыш';
?>

<div>
    <button id="export_essays" class="btn btn-primary pull-right">Выгрузить</button>
    <?php
        echo Html::beginForm('', 'post', ['id' => 'filter-form', 'class' => 'col-xs-5'] );
            echo Html::dropDownList("filter_week", $filter_week, Weeks::getWeeksDropDown() , ['prompt' => 'Неделя розыгрыша', 'class' => 'form-control']);
            echo "<br/>";
            echo Html::dropDownList("filter_status", $filter_status, [1 => 'Номинант'] , ['prompt' => 'Статус обработки', 'class' => 'form-control  ']);
            echo "<br/>";
            echo Html::textInput("filter_lastname", $filter_lastname, ["placeholder" => "Фамилия", "class" => "form-control"]);
            echo "<br/>";
            echo Html::textInput("filter_firstname", $filter_firstname, ["placeholder" => "Имя", "class" => "form-control"]);
            echo "<br/>";
			echo Html::submitButton("Поиск", ["class" => "btn btn-primary"]);
        echo Html::endForm();
    ?>
    <div class="clearfix"></div>
    <br/>
    <?php if(count($model) > 0): 
        echo Html::beginForm(Url::to("/admin/exportmain"), 'post', ['id' => 'essays-form', 'target' => '_blank'] );
    ?>
    
        <table class="table table-hover shell-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check_all"/> <br/>Выбрать все</th>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Номер телефона</th>
                    <th>Дата написания<br/>эссе</th>
                    <th>Текст эссе</th>
                    <th>Статус<br/>обработки</th>
                </tr>
            </thead>
            <?php foreach ($model as $essay):?>
                <tr id="tr_essay_<?=$essay['essay_id'];?>" data-essay-id="<?=$essay['essay_id'];?>">
                    <td><input type="checkbox" name="essay_id[]" value="<?=$essay['essay_id'];?>" class="essay_check"/></td>
                    <td><?=$essay['lastname'];?></td>
                    <td><?=$essay['firstname'];?></td>
                    <td><?=$essay['phone'];?></td>
                    <td><?=date('d.m.Y H:i:s', strtotime($essay['create_date']));?></td>
                    <td class="open_essay"><?=\mb_substr($essay['text'], 0, 10, 'UTF-8');?> ...</td>
                    <td class="open_essay td_status"><?=($essay['is_winner'])?"Победитель":"";?></td>
                    <td style="border:none;border-left:1px solid #dadada"><a href="#" class="btn btn-info open_essay">Фото</a></td>
                    <td class="td_winner <?=($essay['is_winner'])?"hide":"";?>" style="border:none;"><a href="#" class="btn btn-success set_winner">Победитель</a></td>
                </tr>
            <?php endforeach;?>
        </table>
        
        <div id="carousel-modal" class="modal fade">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <!-- Карусель -->
                        <div id="adminCarousel" class="carousel slide">
                            <div class="carousel-inner">
                                <?php foreach ($model as $essay):?>
                                    <div id="essay_<?=$essay['essay_id'];?>" class="item" data-essay-id="<?=$essay['essay_id'];?>" data-essay-status="<?=$essay['status'];?>">
                                        <p style="font-size:24px;">
                                            <b style="font-size:24px;"><?=$essay['firstname']?>, </b>
                                            <b style="font-size:16px;">
                                                <?=$essay['city']?>, 
                                                <?=app\models\User::getYearsOld($essay['birth_date'])?> года
                                            </b>
                                        </p>
                                        <p>
                                            <div class="essay-img-block">
                                                <?=(Essays::getPhoto($essay['photo_path']))?"<img src=".Essays::getPhoto($essay['photo_path']).">":"";?>
                                            </div>
                                            <?=$essay['text']?>
                                        </p>
										<hr style="clear:both"/>     
										<?php $form = ActiveForm::begin([
											'id' => 'essay-form-'.$essay['essay_id'],
                                            'action' => Url::to('\admin\essayphoto'),
											'options' => ['enctype'=>'multipart/form-data', 'class' => 'registr essay-form'],
											'fieldConfig' => [
												'template' => "<div class=\"form-group\">{input}</div>"
											]
										]); ?>
                                            <input type="hidden" name="EssayPhotoForm[essay_id]" value="<?=$essay['essay_id']?>">
                                            <div class="input-item foto-esse input-file">
                                                <div class="fileform">
                                                    <div class="selectbutton">Загрузить фото</div>
                                                    <div class="fileformlabel"></div>
                                                    <input type="file" name="EssayPhotoForm[photo]" class="upload" id="upload-<?=$essay['essay_id'];?>" onchange="getName(this);" />
                                                </div>
                                                <label for="upload-<?=$essay['essay_id'];?>">
                                                    (файл в формате .JPG, не более 3МБ) .
                                                </label>
                                            </div>
										<?php ActiveForm::end(); ?>
                                    </div>
                                <?php endforeach;?>
                            </div>
                            <a class="carousel-control left" href="#adminCarousel" data-slide="prev" style="background:none;">
                              <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="carousel-control right" href="#adminCarousel" data-slide="next" style="background:none;">
                              <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php 
        echo Html::endForm();
        echo LinkPager::widget([
            'pagination' => $pages,
        ]); 
        else:?>
        <p class="alert alert-info">Нет работ</p>
    <?php endif;?>
</div>
