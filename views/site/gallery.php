<?php
use app\models\Essays;
use app\models\Socials;
use app\models\Weeks;
 
$js_swipers = "";
foreach($wEssays as $key=>$uEssays){
    $js_swipers .= ' var swiper'.$key.' = new Swiper(".swiper'.$key.'", {
        slidesPerView: 1,
        spaceBetween: 30,
        keyboardControl: true,
        nextButton: ".swiper-button-next'.$key.'",
        prevButton: ".swiper-button-prev'.$key.'",
        loop: true
    });'; 
}

$this->registerJs('
    selected_essay_id = 0; 
     '.$js_swipers.' 
         
    $("#tabs").tabs();
    $(".open_essay").on("click", function(){
        var item = $(this).closest(".swiper-slide");
		selected_essay_id = item.attr("data-essay-id");
		$("#vote_results").addClass("hidden");
        $("#openwins").find(".title-esse").html(item.find(".title-esse").html());
        $("#openwins").find(".fleft").html(item.find(".fleft").html());
        $("#openwins").find(".fright").html(item.find(".fright").html());
        if(item.find(".text-esse").hasClass("without-foto")){
            $("#openwins").find(".text-esse").addClass("without-foto");
        }
        else{
            $("#openwins").find(".text-esse").removeClass("without-foto");
        }
        $("#essay_votes").html(item.attr("data-essay-votes"));
        $.fancybox({"href" : "#openwins",  topRatio :0});
        return false;
    })

    $(".essay_vote").on("click", function(){
        var item = $(this).closest(".swiper-slide");
		selected_essay_id = item.attr("data-essay-id");
		$("#vote_results").addClass("hidden");
        $.fancybox({"href" : "#auto-soc"});
        return false;
    })

    $(".modal_essay_vote").on("click", function(){
        $.fancybox({"href" : "#auto-soc"});
        return false;
    })
	
	$("#fb_vote").on("click", function(){
		fbVote(selected_essay_id);
        return false;
	})
	
	$("#ok_vote").on("click", function(){
		okVote(selected_essay_id);
        return false;
	})
	
	$("#vk_vote").on("click", function(){
		vkVote(selected_essay_id);
        return false;
	})
	
', yii\web\View::POS_READY);

$this->registerJs('
	function fbVote(essay_id) {
		var pre_url = "' . Socials::generateFbLink() . '";
		var redirect_uri = "&redirect_uri=' . urlencode(\yii\helpers\Url::to(["site/vote", "soc_type" => "fb"], true)."&") . 'essay_id=" + essay_id;
		var url = pre_url + redirect_uri;
		poptastic(url, "fb_auth");
	}  
    
	function okVote(essay_id) {
		var pre_url = "' . Socials::generateOkLink() . '";
		var redirect_uri = "&redirect_uri=' . urlencode(\yii\helpers\Url::to(["site/vote", "soc_type" => "ok"], true)."&") . 'essay_id=" + essay_id;
		var url = pre_url + redirect_uri;
		poptastic(url, "ok_auth");
	}  
    
	function vkVote(essay_id) {
		var pre_url = "' . Socials::generateVkLink() . '";
		var redirect_uri = "&redirect_uri=' . urlencode(\yii\helpers\Url::to(["site/vote", "soc_type" => "vk"], true)."&") . 'essay_id=" + essay_id;
		var url = pre_url + redirect_uri;
		poptastic(url, "vk_auth");
	}  
	
	function poptastic(url, name) {
        var left = (screen.width/2)-(600/2);
        var top = (screen.height/2)-(500/2);
		var newWindow = window.open(url, name, "height=500,width=600, top="+top+", left="+left);
		if (window.focus) {
			newWindow.focus();
		}
    }
	
	function setVotePlus() {
        var item = $("#essay_" + selected_essay_id);
        var c_votes = (parseFloat(item.attr("data-essay-votes")) + 1);
        item.attr("data-essay-votes", c_votes);
        item.find(".count_votes").html(c_votes);
        $("#essay_votes").html(c_votes);
    }
	
	function setVoteResult(status, text) {
		if(status == "error"){
			$("#vote_results").html(text).removeClass("hidden");
		}else{
            setVotePlus();
            $.fancybox.close();
            $.fancybox({"href" : "#thanks"});
		}
    }
	
', yii\web\View::POS_HEAD);

$this->title = 'Голосование';
?>

<div class="content-gallery">
	<div class="img-wrapper">
		<img src="/img/galery-main.jpg" alt="Фото машины">
	</div>
	<div class="text-wrapper">
		<p><strong>
			Рады приветствовать вас на нашей странице голосования! Еженедельно вы можете ознакомиться здесь с лучшими конкурсными работами, прошедшими специальный отбор,  проголосовать за наиболее интересный рассказ и поддержать участников программы на их пути к достижению своих целей вместе с Shell Rimula.
		</strong></p>
	</div>
	<?php if(count($wEssays) > 0):?>
    
        <div class="gallery-wrapper" id="tabs">
            <div class="nav-gallery">
                <ul>
                    <?php 
					$i = 0;
					foreach($wEssays as $key=>$uEssays):
					$i++;
					?>
                        <li class="<?=(Weeks::isCurrentVote($key))?'ui-tabs-active ui-state-active':'';?>"><a href="#week_<?=$i;?>" >Неделя<br/>с <?=date('d.m.Y', strtotime($weeks[$key]['date_vote_start']));?> по <?=date('d.m.Y', strtotime($weeks[$key]['date_vote_end']));?></a></li>
                    <?php endforeach;?>
                </ul>
            </div>

			<?php 
				$i = 0;
				foreach($wEssays as $key=>$uEssays):
				$i++;
				?>
				<div class="tabs-content swiper-container swiper<?=$i ;?>" id="week_<?=$i ;?>">
                    <div class="swiper-button-next swiper-button-next<?=$i ;?>"></div>
                    <div class="swiper-button-prev swiper-button-prev<?=$i ;?>"></div>
                    <div class="swiper-wrapper">
                        <?php foreach ($uEssays as $essay):?>
                        <div id="essay_<?=$essay['essay_id'];?>" class="swiper-slide" data-essay-id="<?=$essay['essay_id'];?>" data-essay-votes="<?=$essay['c_votes'];?>">
                            <div class="item-gallery">
                                <div class="content-esse">
                                    <div class="title-esse">
                                        <p><strong><?=$essay['firstname']?></strong> / <?=$essay['city']?> / <?=app\models\User::getYearsOld($essay['birth_date'])?> <?= app\components\ShellHelper::YearTextArg(app\models\User::getYearsOld($essay['birth_date']))?> <?=($essay['c_votes'] > 0)?'<span style="float:right">Количество голосов: <span class="count_votes">'.$essay['c_votes'].'</span></span>':''?></p>
                                    </div>
                                    <div class="text-esse <?=(Essays::getPhoto($essay['photo_path']))?'':'without-foto'?>">
                                        <div class="fleft">
                                            <p><?=$essay['text']?></p>
                                        </div>			
                                        <div class="fright">
                                            <?=(Essays::getPhoto($essay['photo_path']))?"<img src='".Essays::getPhoto($essay['photo_path'])."'  alt='Фото победителя' >":"";?>
                                        </div>
                                    </div>
                                    <div class="opros-esse">
                                        <a href="#openwins" class="href-esse open_essay">Читать полностью</a>
                                        <a href="#auto-soc" class="href-esse essay_vote">Проголосовать за работу</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach;?>
                    </div>
				</div>
			<?php endforeach;?>
		</div>
		
    <div class="hidden">
		<div id="auto-soc">
			<p>
				Пожалуйста, авторизуйтесь через удобную Вам социальную сеть, чтобы проголосовать за понравившуюся работу участника акции.
			</p>
			<div class="item-soc">
				<a href="#" class="items-soc" id="fb_vote"></a>
				<a href="#" class="items-soc" id="vk_vote"></a>
				<a href="#" class="items-soc" id="ok_vote"></a>
			</div>
            <p id="vote_results" class="text-info hidden"></p>
		</div>
	</div>
    
	<div class="hidden">
		<div id="openwins">
			<div class="content-esse">
				<div class="title-esse">
				</div>
				<div class="text-esse">
					<div class="fleft">
                    </div>				
					<div class="fright">
					</div>
				</div>
				<div class="opros-esse">
					<span class="golos-with-href">Количество голосов: <span id="essay_votes">0</span></span> 
                    <a href="#auto-soc" class="href-esse modal_essay_vote">Проголосовать за работу</a>
				</div>	
			</div>
		</div>
	</div>
    
	<div class="hidden">
		<div id="thanks">
			<div class="content-esse">
                <p class="text-center">Спасибо! Ваш голос принят!</p>
			</div>
		</div>
	</div>
    
	<?php else:?>
		<p>На данный момент идет сбор заявок</p>
	<?php endif;?>
</div>
