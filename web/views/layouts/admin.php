<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AdminAsset;
use yii\helpers\Url;

AdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    $nav_hidden = (Yii::$app->request->url == '/admin/login')?'hidden':'';
    
    NavBar::begin([
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top ' . $nav_hidden,
        ],
    ]);
    $items = [
            ['label' => 'Участники', 'url' => ['/admin/users']],
            ['label' => 'Модерация работ', 'url' => ['/admin/index']],
            ['label' => 'Еженедельный розыгрыш', 'url' => ['/admin/weekwin']],
            ['label' => 'Главный розыгрыш', 'url' => ['/admin/mainwin']],
            ['label' => 'Статистика', 'url' => ['/admin/statistic']],
            ['label' => 'Акция', 'url' => ['/site/index']],
            [
               'label' => 'Выход',
               'url' => Url::to('/site/logout'),
               'linkOptions' => ['data-method' => 'post']
            ],
        ];
    
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => $items,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= $content ?>
    </div> 
</div> 

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
