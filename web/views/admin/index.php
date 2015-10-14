<?php
use app\models\Essays;
use app\models\Weeks;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJs('
    function checkEssayStatus(){
        var essay_status = $("#adminCarousel").find(".item.active").attr("data-essay-status");
        $(".essay_buttons, .essay_confirmed, .essay_canceled").addClass("hide");
        if(essay_status == 1){
            $(".essay_buttons").removeClass("hide");
        }        
        else if(essay_status == 2){
            $(".essay_confirmed").removeClass("hide");
        }      
        else if(essay_status == 3){
            $(".essay_canceled").removeClass("hide");
        }
    }
    
    function cangeStatus(e_id, status){
        return $.ajax({
            dataType: "json",
            data: "changeStatus=1&essay_id=" + e_id + "&status=" + status,
            success: function(data){
                if(!(data.status && data.status == "ok")){
                    alert("Не получилось изменить статус");
                    return false;
                }
                else{
                    return true;
                }
            },
            error: function(data){
                alert("Не получилось изменить статус");
                return false;
            }
        })
    }
    
    $("#adminCarousel").carousel("pause");
    
    $(".open_essay").on("click", function(){
        var essay_id = $(this).closest("tr").attr("data-essay-id");
        $("#adminCarousel .item").removeClass("active");
        $("#essay_"+essay_id).addClass("active");
        checkEssayStatus();
        $("#carousel-modal").modal("show");
    })
    
    $("#adminCarousel").on("slide.bs.carousel", function () {
        $(".essay_buttons, .essay_confirmed, .essay_canceled").addClass("hide");
    })
    
    $("#adminCarousel").on("slid.bs.carousel", function () {
        checkEssayStatus();
    })
    
    $("#essay_confrim").on("click", function () {
        var essay_id = $("#adminCarousel").find(".item.active").attr("data-essay-id");
        if(cangeStatus(essay_id, 2)){
            $("#carousel-modal").modal("show");
            $("#tr_essay_" + essay_id).find(".td_status").html("Принято");
        }
        $(".essay_buttons, .essay_canceled").addClass("hide");
        $(".essay_confirmed").removeClass("hide");
    })
    
    $("#essay_cancel").on("click", function () {
        var essay_id = $("#adminCarousel").find(".item.active").attr("data-essay-id");
        if(cangeStatus(essay_id, 3)){
            $("#carousel-modal").modal("show");
            $("#tr_essay_" + essay_id).find(".td_status").html("Не принято");
        }
        $(".essay_buttons, .essay_confirmed").addClass("hide");
        $(".essay_canceled").removeClass("hide");
    })

    $("#filter-form").on("change", function(){
        this.submit();
    })
', yii\web\View::POS_READY);

$this->title = 'Модерация работ';
?>

<div>
    <?php
        echo Html::beginForm('', 'post', ['id' => 'filter-form', 'class' => 'col-md-3'] );
        echo Html::dropDownList("filter_week", $filter_week, Weeks::getWeeksDropDown() , ['prompt' => 'Неделя розыгрыша', 'class' => 'form-control']);
        echo "<br/>";
        echo Html::dropDownList("filter_status", $filter_status, [1 => 'Не обработано', 2 => 'Принято', 3 => 'Не принято'] , ['prompt' => 'Статус обработки', 'class' => 'form-control  ']);
        echo Html::endForm();
    ?>
    <div class="clearfix"></div>
    <br/>
    <?php if(count($model) > 0): ?>
    
        <table class="table table-hover shell-table">
            <thead>
                <tr>
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
                    <td><?=$essay['lastname'];?></td>
                    <td><?=$essay['firstname'];?></td>
                    <td><?=$essay['phone'];?></td>
                    <td><?=date('d.m.Y H:i:s', strtotime($essay['create_date']));?></td>
                    <td class="open_essay"><?=\mb_substr($essay['text'], 0, 10, 'UTF-8');?> ...</td>
                    <td class="open_essay td_status"><?=Essays::getStatusName($essay['status']);?></td>
                    <td style="border:none;border-left:1px solid #dadada;"><button class="btn btn-info open_essay">фото</button></td>
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
                                            <?=(Essays::getPhoto($essay['photo_path']))?"<img src=".Essays::getPhoto($essay['photo_path']).">":"";?>
                                            <?=$essay['text']?>
                                        </p>
                                    </div>
                                <?php endforeach;?>
                            </div>
                            <a class="carousel-control left" href="#adminCarousel" data-slide="prev">
                              <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="carousel-control right" href="#adminCarousel" data-slide="next">
                              <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <p class="text-center essay_buttons hide">
                            <button id="essay_confrim" class="btn btn-success">Принять</button>
                            <button id="essay_cancel" class="btn btn-danger">Отклонить</button>
                        </p>
                        <p class="essay_confirmed text-center alert alert-success hide">Принято</p>
                        <p class="essay_canceled text-center alert alert-danger hide">Не принято</p>
                    </div>
                </div>
            </div>
        </div>
    <?php 
        echo LinkPager::widget([
            'pagination' => $pages,
        ]); 
        else:?>
        <p class="alert alert-info">Нет работ</p>
    <?php endif;?>
</div>
