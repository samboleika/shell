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
	<meta name="datalayer" content=""
	data-meta-home=""
	data-meta-page-path="/<?=Yii::$app->controller->getRoute()?>"
	data-meta-page-title="<?=Html::encode($this->title)?>"
	data-meta-country="ru"
	data-meta-language="ru"
	data-meta-template="<?=(Yii::$app->controller->getRoute() == 'site/error')?'error-page':'contentpage'?>">
	
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
	<script src="//assets.adobedtm.com/9b74220da1d0b361973dbd26a530b8f49255d00c/satelliteLib-a7cca9856cc4669076d5dd58c2c2b572666b6e85-staging.js"></script>
</head>
<body>
<?php $this->beginBody() ?>
	<section class="container">
        <header>
            <div class="navi">
                <ul>
                    <li><a href="/site/index" class="<?=(Yii::$app->request->url == '/site/index')?'active':''?>">Главная</a></li><li>
                    <a href="/site/about" class="<?=(Yii::$app->request->url == '/site/about')?'active':''?>">Правила</a></li><li>
                    <a href="/site/login" class="<?=(Yii::$app->request->url == '/site/login')?'active':''?>">Участвовать</a></li><li>
                    <a href="/site/gallery" class="<?=(Yii::$app->request->url == '/site/gallery')?'active':''?>">Голосование</a></li><li>
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
    <script type="text/javascript">
		_satellite.pageBottom();
	
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-69522492-1', 'auto');
		ga('send', 'pageview');

  </script>
</body>
</html>
<?php $this->endPage() ?>
