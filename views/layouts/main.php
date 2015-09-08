<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;

AppAsset::register($this);
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
    NavBar::begin([
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $items = [
            ['label' => 'Главная', 'url' => ['/site/index']],
            ['label' => 'Правила', 'url' => ['/site/about']],
            ['label' => 'Участвовать', 'url' => ['/site/login']],
            ['label' => 'Галлерея', 'url' => ['/site/gallery']],
            ['label' => 'Победители', 'url' => ['/site/winners']],
            ['label' => 'Обратная связь', 'url' => ['/site/contact']]
        ]; 
    
    if(!\Yii::$app->user->isGuest){
        if(Yii::$app->user->identity->isModerator() || Yii::$app->user->identity->isClient()){
            $items[] = ['label' => 'Админ', 'url' => ['/admin/index']];
        }
        $items[] = [
                'label' => 'Выход (' . Yii::$app->user->identity->username . ')',
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post']
            ];
    }
    
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
