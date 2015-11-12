<?php
use app\models\Essays;
?>
<div class="title-esse">
    <p><strong><?=$winner['firstname']?></strong> / <?=$winner['city']?> / <?=app\models\User::getYearsOld($winner['birth_date'])?> <?= app\components\ShellHelper::YearTextArg(app\models\User::getYearsOld($winner['birth_date']))?></p>
</div>
<div class="text-esse <?=(Essays::getPhoto($winner['photo_path']))?'':'without-foto'?>">
    <div class="fleft">
        <p><?=$winner['text']?></p>
    </div>				
    <div class="fright">
        <?=(Essays::getPhoto($winner['photo_path']))?"<img src='".Essays::getPhoto($winner['photo_path'])."'  alt='Фото победителя' >":"";?>
    </div>
</div>
<div class="opros-esse">
    <span>Количество голосов:</span> <span><?=$winner['c_votes']?></span>
</div>