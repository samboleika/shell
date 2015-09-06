<?php
use app\models\Essays;
use app\models\Weeks;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJs('
    $(".set_nominee").on("click", function(){
        var _this = $(this);
        var essay_id = $(this).closest("tr").attr("data-essay-id");
        $.ajax({
            dataType: "json",
            data: "setNominee=1&essay_id=" + essay_id,
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
                    _this.closest("tr").find(".td_status").html("Номинант");
                    _this.closest("tr").find(".td_nominee").hide();
                    return true;
                }
            },
            error: function(data){
                alert("Не получилось изменить статус");
                return false;
            }
        })
    })
    
    $("#adminCarousel").carousel("pause");
    
    $(".open_essay").on("click", function(){
        var essay_id = $(this).closest("tr").attr("data-essay-id");
        $("#adminCarousel .item").removeClass("active");
        $("#essay_"+essay_id).addClass("active");
        $("#carousel-modal").modal("show");
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
    })
    
', yii\web\View::POS_READY);

$this->title = 'Еженедельный розыгрыш';
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
        echo Html::endForm();
    ?>
    <div class="clearfix"></div>
    <br/>
    <?php if(count($model) > 0): 
        echo Html::beginForm(Url::to("/admin/exportweek"), 'post', ['id' => 'essays-form', 'target' => '_blank'] );
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
                    <td class="open_essay td_status"><?=($essay['is_nominee'])?"Номинант":"";?></td>
                    <td style="border:none;border-left:1px solid #dadada"><button class="btn btn-info open_essay">Фото</button></td>
                    <td class="td_nominee <?=($essay['is_nominee'])?"hide":"";?>" style="border:none;"><button class="btn btn-success set_nominee">Номинант</button></td>
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
