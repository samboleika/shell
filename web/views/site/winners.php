<?php

use yii\helpers\Html;

$this->registerJs("
    $('.open_essay').on('click', function(){
        var item = $(this).closest('tr');
        $.ajax({
            url:'/site/winnerinfo',
            type: 'POST',
            data: 'essay_id=' + item.attr('data-essay-id'),
            success: function( data ) {
                $('#openwins .content-esse').html(data);
                $.fancybox({'href' : '#openwins'});
            }
        });
        return false;
    })
", yii\web\View::POS_READY);

$this->title = 'Победители';
?>

<div class="content-wins">
    <?php if(count($main_winners)):?>
    <div class="img-wrapper">
        <img src="/img/wins-main.jpg" alt="Победители">
    </div>
    <div class="table">
        <h3>Победители заключительного этапа</h3>
        <table class="table-finish">
            <thead>
                <tr>
                    <td>Победитель</td>
                    <td>Ссылка на работу</td>
                </tr>						
            </thead>
            <tbody>
                <?php foreach ($main_winners as $main_winner):?>
                <tr data-essay-id="<?=$main_winner['essays_id']?>">
                    <td><?=$main_winner['firstname']?>, <?=app\models\User::getYearsOld($main_winner['birth_date'])?> года, <?=$main_winner['city']?></td>
                    <td><a href="#" class="open_essay btn btn-link">Ссылка на работу</a></td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    <?php endif;?>
    
    <?php if(count($weeks)):?>
    <div class="table">
        <h3>Победители промежуточных этапов</h3>
        <table class="table-part">
            <thead>
                <tr>
                    <td>Период</td>
                    <td>Победитель</td>
                    <td>Ссылка на работу</td>
                </tr>						
            </thead>
            <tbody>
                <?php 
                foreach ($weeks as $week):
                $winner = \app\models\Essays::getWeekWeener($week['id']);
                if(!empty($winner)):
                ?>
                <tr data-essay-id="<?=$winner['essay_id']?>">
                    <td>Неделя <?=$week['id']?></td>
                    <td><?=$winner['firstname']?>, <?=app\models\User::getYearsOld($winner['birth_date'])?> года, <?=$winner['city']?></td>
                    <td><a href="#" class="open_essay btn btn-link">Ссылка на работу</a></td>
                </tr>
                <?php 
                endif;
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
    <?php endif;?>
</div>

<!-- essay-modal -->
<div class="hidden">
    <div id="openwins">
        <div class="img-main-fancybox">
            <img src="/img/wins-main-fancybox.jpg" alt="">
        </div>
        <div class="content-esse">            
        </div>
</div>
