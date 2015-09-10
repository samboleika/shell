<?php
use app\models\Essays;
use app\models\Socials;

$this->registerJs('
    selected_essay_id = 0;
	
    $(".frontCarusel").carousel("pause");
    
    $(".open_essay").on("click", function(){
        var item = $(this).closest(".item");
		selected_essay_id = item.attr("data-essay-id");
		$("#vote_results").addClass("hide");
        $("#essay-modal").find(".modal-body").html(item.find(".essay-content").html());
        $("#essay_votes").html(item.attr("data-essay-votes"));
        $("#essay-modal").modal("show");
        return false;
    })

    $(".essay_vote").on("click", function(){
        var item = $(this).closest(".item");
		selected_essay_id = item.attr("data-essay-id");
		$("#vote_results").addClass("hide");
        $("#vote-modal").modal("show");
        return false;
    })

    $(".modal_essay_vote").on("click", function(){
        $("#vote-modal").modal("show");
        return false;
    })
	
	$("#fb_vote").on("click", function(){
		fbVote(selected_essay_id);
	})
	
	$("#ok_vote").on("click", function(){
		okVote(selected_essay_id);
	})
	
	$("#vk_vote").on("click", function(){
		vkVote(selected_essay_id);
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
		var newWindow = window.open(url, name, "height=600,width=450");
		if (window.focus) {
			newWindow.focus();
		}
    }
	
	function setVotePlus() {
        var item = $("#essay_" + selected_essay_id);
        var c_votes = (parseFloat(item.attr("data-essay-votes")) + 1);
        item.attr("data-essay-votes", c_votes);
        $("#essay_votes").html(c_votes);
    }
	
	function setVoteResult(status, text) {
		if(status == "error"){
			$("#vote_results").html(text).removeClass("hide");
		}else{
            setVotePlus();
			$("#vote-modal").modal("hide");
		}
    }
	
', yii\web\View::POS_HEAD);

$this->title = 'Галерея';
?>

<div> 
	<?php if(count($wEssays) > 0):?>
	
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<?php for($i = 1;$i <= count($wEssays); $i++):?>
				<li role="presentation" class="<?=($i == 1)?'active':'';?>"><a href="#week_<?=$i ;?>" aria-controls="home" role="tab" data-toggle="tab">Неделя <?=$i?></a></li>
			<?php endfor;?>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content" style="border: 1px solid #dadada; border-top:none; padding: 15px;">
			<?php foreach($wEssays as $key=>$uEssays):?>
				<div role="tabpanel" class="tab-pane <?=($key == 1)?'active':'';?>" id="week_<?=$key ;?>">
					<!-- Карусель -->
					<div class="carousel slide frontCarusel">
						<div class="carousel-inner">
							<?php foreach ($uEssays as $key=>$essay):?>
                            <div id="essay_<?=$essay['essay_id'];?>" class="item <?=($key == 0)?'active':'';?>" data-essay-id="<?=$essay['essay_id'];?>" data-essay-votes="<?=$essay['c_votes'];?>">
                                <div class="essay-content">
                                    <p style="font-size:24px;">
                                        <b style="font-size:24px;"><?=$essay['firstname']?>, </b>
                                        <b style="font-size:16px;">
                                           <span class="city"><?=$essay['city']?></span>, 
                                           <?=app\models\User::getYearsOld($essay['birth_date'])?> года
                                        </b>
                                    </p>
                                    <p>
                                        <?=(Essays::getPhoto($essay['photo_path']))?"<img src=".Essays::getPhoto($essay['photo_path']).">":"";?>
                                        <div class="essay_text">
                                           <?=$essay['text']?>
                                        </div>
                                    </p>
                                <div class="clearfix"></div>
                                </div>
                                <div class="essay_actions text-center">
                                    <button class="btn open_essay">Читать полностью</button>
                                    <button class="btn essay_vote">Проголосовать за работу</button>
                                </div>
                            </div>
							<?php endforeach;?>
						</div>
						<a class="carousel-control left" href=".frontCarusel" data-slide="prev">
                     <span class="glyphicon glyphicon-chevron-left"></span>
						</a>
						<a class="carousel-control right" href=".frontCarusel" data-slide="next">
                     <span class="glyphicon glyphicon-chevron-right"></span>
						</a>
					</div>
				</div>
			<?php endforeach;?>
		</div>
		
    <!-- essay-modal -->
    <div id="essay-modal" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <span>Количество голосов:<b id="essay_votes">0</b></span>
                        <button class="btn modal_essay_vote">Проголосовать за работу</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
    <!-- vote-modal -->
    <div id="vote-modal" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
					<div class="text-center">
						<p>Авторизуйся через свою социальную сеть,	чтобы проголосовать за работу</p>
						<button class="btn btn-primary" id="fb_vote">Facebook</button> 
						<button class="btn btn-primary" id="ok_vote">Odnoklassniki</button> 
						<button class="btn btn-primary" id="vk_vote">Vkontakte</button> 
						<p id="vote_results" class="text-info hide"></p>
					</div>
                </div>
            </div>
        </div>
    </div>
    
	<?php else:?>
		<p>Нет работ</p>
	<?php endif;?>
</div>
