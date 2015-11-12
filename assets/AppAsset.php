<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/site.css',
        'css/fonts.css',
        'css/main.css',
        'css/media.css',
        'libs/font-awesome-4.2.0/css/font-awesome.min.css',
        'libs/fancybox/jquery.fancybox.css',
        'libs/owl-carousel/owl.carousel.css',
        'libs/swiper/swiper.css',
    ];
    public $js = [
        'js/common.js',
        'js/datalayer.js',
        'libs/fancybox/jquery.fancybox.pack.js',
        'libs/jquery-ui/jquery-ui.js',
        'libs/jquery.waypoints.min.js',
        'libs/owl-carousel/owl.carousel.min.js',
        'libs/swiper/swiper.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
