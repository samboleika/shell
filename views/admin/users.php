<?php
use app\models\Essays;
use app\models\Weeks;
use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Участники';
?>

<div>
    <?php
        echo Html::beginForm('', 'post', ['id' => 'filter-form', 'class' => 'col-md-3'] );
            echo "<br/>";
            echo Html::textInput("filter_lastname", $filter_lastname, ["placeholder" => "Фамилия", "class" => "form-control"]);
            echo "<br/>";
            echo Html::textInput("filter_firstname", $filter_firstname, ["placeholder" => "Имя", "class" => "form-control"]);
            echo "<br/>";
			echo \yii\widgets\MaskedInput::widget([
				'name' => 'filter_phone',
                'mask' => '+7 (999) 999-99-99',
				'value' => $filter_phone,
                'options' => [
					'placeholder' => '+7 (___) ___-__-__',
					'class' => 'form-control'
				]
			]);
            echo "<br/>";
			echo Html::submitButton("Поиск", ["class" => "btn btn-primary"]);
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
                    <th>Дата рождения</th>
                    <th>Номер телефона</th>
                    <th>Город</th>
                    <th>Дата регистрации</th>
                </tr>
            </thead>
            <?php foreach ($model as $user):?>
                <tr>
                    <td><?=$user['lastname'];?></td>
                    <td><?=$user['firstname'];?></td>
                    <td><?=date("d.m.Y", strtotime($user['birth_date']));?></td>
                    <td><?=$user['phone'];?></td>
                    <td><?=$user['city'];?></td>
                    <td><?=($user['date_create'])?date("d.m.Y", strtotime($user['date_create'])):'';?></td>
                </tr>
            <?php endforeach;?>
        </table>
        
    <?php 
        echo LinkPager::widget([
            'pagination' => $pages,
        ]); 
        else:?>
        <p class="alert alert-info">Нет участников</p>
    <?php endif;?>
</div>
