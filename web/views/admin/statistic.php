<?php
use app\models\Essays;
use app\models\Weeks;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJs('
    
    $("#filter-form").on("change", function(){
        this.submit();
    })
    
', yii\web\View::POS_READY);

$this->title = 'Статистика';
?>

<div>
    <?php
        echo Html::beginForm('', 'post', ['id' => 'filter-form', 'class' => 'col-xs-5'] );
            echo Html::dropDownList("filter_week", $filter_week, Weeks::getWeeksDropDown() , ['prompt' => 'Неделя розыгрыша', 'class' => 'form-control']);
        echo Html::endForm();
    ?>
    <div class="clearfix"></div>
    <br/>
    <?php if(count($model) > 0): ?>
    <p>Работ зарегистрировано: <span><?=$model['allcount']?></span></p>
    <p>Прошло модерацию <span><?=$model['confirmed']?></span></p>
    <p>Отклонено при модерации: <span><?=$model['declined']?></span></p>
    <?php else:?>
        <p class="alert alert-info">Нет работ</p>
    <?php endif;?>
</div>
