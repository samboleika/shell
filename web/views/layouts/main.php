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
	<section class="container">
        <header>
            <div class="tagline">
                <p>На пути к цели с Shell Rimula!</p>
                <p>Участвуйте в акции от Shell Rimula в период с 2 ноября по 27 декабря 2015 года </p>					
            </div>
            <div class="navi">
                <ul>
                    <li><a href="/site/index" class="<?=(Yii::$app->request->url == '/site/index')?'active':''?>">Главная</a></li><li>
                    <a href="/site/about" class="<?=(Yii::$app->request->url == '/site/about')?'active':''?>">Правила</a></li><li>
                    <a href="/site/login" class="<?=(Yii::$app->request->url == '/site/login')?'active':''?>">Участвовать</a></li><li>
                    <a href="/site/gallery" class="<?=(Yii::$app->request->url == '/site/gallery')?'active':''?>">Галерея</a></li><li>
                    <a href="/site/winners" class="<?=(Yii::$app->request->url == '/site/winners')?'active':''?>">Победители</a></li><li>
                    <a href="/site/contact" class="<?=(Yii::$app->request->url == '/site/contact')?'active':''?>">Обратная связь</a></li>
                </ul>
            </div>
        </header>
        <div class="wrap">
            <div class="container">
                <?= $content ?>
            </div> 
        </div>
	</section>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
