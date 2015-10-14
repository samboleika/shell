function getName (str){
    if (str.lastIndexOf('\\')){
        var i = str.lastIndexOf('\\')+1;
    }
    else{
        var i = str.lastIndexOf('/')+1;
    }						
    var filename = str.slice(i);			
    var uploaded = document.getElementById("fileformlabel");
    uploaded.innerHTML = filename;
}
$(document).ready(function() {

    //Попап менеджер FancyBox
    //Документация: http://fancybox.net/howto
    //<a class="fancybox"><img src="image.jpg" /></a>
    //<a class="fancybox" data-fancybox-group="group"><img src="image.jpg" /></a>
    $(".fancybox").fancybox();
    //Добавляет классы дочерним блокам .block для анимации
    //Документация: http://imakewebthings.com/jquery-waypoints/
    $(".block").waypoint(function(direction) {
            if (direction === "down") {
                    $(".class").addClass("active");
            } else if (direction === "up") {
                    $(".class").removeClass("deactive");
            };
    }, {offset: 100});

    //Каруселька
    //Документация: http://owlgraphic.com/owlcarousel/
    var owl = $(".carousel");
    owl.owlCarousel({
            items : 4
    });
    owl.on("mousewheel", ".owl-wrapper", function (e) {
            if (e.deltaY > 0) {
                    owl.trigger("owl.prev");
            } else {
                    owl.trigger("owl.next");
            }
            e.preventDefault();
    });
    $(".next_button").click(function(){
            owl.trigger("owl.next");
    });
    $(".prev_button").click(function(){
            owl.trigger("owl.prev");
    });

    //Кнопка "Наверх"
    //Документация:
    //http://api.jquery.com/scrolltop/
    //http://api.jquery.com/animate/
    $("#top").click(function () {
            $("body, html").animate({
                    scrollTop: 0
            }, 800);
            return false;
    });
});
	