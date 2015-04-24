
function toggleMobileMenu(){
        
    var hidden_style = {'-ms-transform':'rotate(0deg)','-webkit-transform':'rotate(0deg)','transform':'rotate(0deg)'};
    var displayed_style = {'-ms-transform':'rotate(180deg)','-webkit-transform':'rotate(180deg)','transform':'rotate(180deg)'};
    if( jQuery(window).width() < 768 ){
        //check if down
        if( jQuery(".header-container > .header-bottom > .nav-container").hasClass("displayed") ){
            jQuery(".header-container > .header-bottom > .nav-container").slideUp(400,function(){
                jQuery(".header-container > .header-bottom > .nav-container").removeClass("displayed");
                //jQuery("#mobile-menu-toggle").css( hidden_style );
            });
        } else {
            jQuery(".header-container > .header-bottom > .nav-container").slideDown(400,function(){
                jQuery(".header-container > .header-bottom > .nav-container").addClass("displayed");
                //jQuery("#mobile-menu-toggle").css( displayed_style );
            });
        }
    }
}


(function($) {	
    function BestsellerSlideshow() {
        $('#slideshow_bestseller').flexslider({
            animation: "slide",
            animationLoop: false,
            itemWidth: 196,
            minItems: 4,
            controlNav: false,
            directionNav: true,
            maxItems: 4,
            start: function(slider){
              $('body').removeClass('loading');
            }
      });
    }
	
    $(window).bind('load', function() {
    	BestsellerSlideshow();
    });
})(jQuery);

function showHoverHelperClassHover(elem,newText){
    if( jQuery(elem).hasClass("show-helper") ){
        jQuery(elem).fadeOut(150,function(){ jQuery(elem).text(newText).fadeIn(); });
    }
}

function showHoverHelperClassHoverOff(elem,oldText){
    if( jQuery(elem).hasClass("show-helper") ){
        jQuery(elem).fadeOut(150,function(){ jQuery(elem).text(oldText).fadeIn(); });
    }
}

jQuery(document).ready(function($){

    //'%3Cesi:include src=%22http://sandbox.needplumbingsupplies.com/varnishcache/getformkey/%22 /%3E/"'.replace(/\+/g, $("input[name='form_key']").val());
    console.log($("input[name='form_key']").val());
   
    function standardizeMainNav(){
        if( $(window).width() > 768 ){
            var navfullWidth = $("#nav").innerWidth();
            var navMenuItems = $("#nav > li").length;

            $("#nav > li").each(function(){
                $(this).css("width", navfullWidth / navMenuItems );
            });
        } else {
            $("#nav > li").each(function(){
                $(this).css("width", "100%" );
            });
        }
    }

    //mobile settings / css
    function setMobile(width){
        if( width < 768 ){

            //get menu height
            var height = 0;
            $(".header-container > .header-bottom > .nav-container > ul").children('li').each(function(){
                height += $(this).height();
            });
        }
    }

    //hide the page overlay and message boxes
    function hideMessageOverlay(){
        //if overlay is not hidden
        if( !$("#page-overlay-dark").hasClass("hidden") ){

            //add the class to fade it out
            $("#page-overlay-dark").addClass("fade-out");
            $("ul.messages").addClass("fade-out");

            //after half a second add the hidden class
            setTimeout(function() {
                $("#page-overlay-dark").addClass("hidden");
                $("ul.messages").addClass("hidden");
            }, 1000);
        }
    }

    function fixTwoColLayout(windowWidth) {
        var mainWidth = $(window).width()-200;
        if( (windowWidth > 479 )  && ( $(".col-main").width() < mainWidth ) ){
            $(".col-main").css("width",mainWidth-70);
        }
    }

    //setup the back to top button
    var offset = 300,
        offset_opacity = 1200,
        scroll_top_duration = 700,
        $back_to_top = $('#back-to-top');

    //hide or show the "back to top" link
    $(window).scroll(function(){ 
        ( $(this).scrollTop() > offset ) ? $back_to_top.addClass('cd-is-visible') : $back_to_top.removeClass('cd-is-visible cd-fade-out');
        if( $(this).scrollTop() > offset_opacity ) { 
            $back_to_top.addClass('cd-fade-out');
        }
    });

    //smooth scroll to top
    $back_to_top.on('click', function(event){
        event.preventDefault();
        $('body,html').animate({
            scrollTop: 0 ,
            }, scroll_top_duration
        );
    });



    //set mobile 
    setMobile( $(window).width() );

    //instantiate fancybox
    $(".fancybox, .prd-fancybox").fancybox({
        topRatio        : .1,
        cyclic          : true,
        autoScale       : true,
        showCloseButton : true,
        showNavArrows   : true,
        scrolling       : 'yes',
        helpers         : {
            overlay : {
                css : {
                    "height" : $(window).height(),
                    'background' : 'rgba(87, 202, 237, 0.3)'
                }
            }
        },
    }); 
      
    $(".prd-fancybox > img").addClass("tooltip");
    $(".prd-fancybox > img").attr("title","Click for larger view");

    //instantiate tooltips
    $(".tooltips").tooltip({});
    $(".tooltip").tooltip({});

    //clear search
    $("#clear-search").click(function(){
        if( $("#search").val() !== "Search entire store here..." ){
            $("#search").val('');
        }
    });

    //show page overlay if there is a page message
    if ($("ul.messages").length > 0){
      $("#page-overlay-dark").removeClass("hidden");
    }
    //on click of page overlay dark hide the overlay and the messages
    $("#page-overlay-dark").click(function(){
        hideMessageOverlay();   
    });

    //hide the overlay on esc push
    $(document).keyup(function(e) {
        //if escape key is pressed
        if (e.keyCode == 27) { 
            hideMessageOverlay(); 
            $("#mageworxOverlay").css({"display":"none"});
        }  
    });

    //adds content to anything that someone copies if they are not flagged as exceptions
    if( $.inArray($("#user-ip").data('uip'), ["50.255.234.190", "50.255.234.189", "50.255.234.188", "50.255.234.187", "50.255.234.186", "50.255.234.185", "127.0.0.1"] ) < 0 ){
        $("body").bind('copy', function (e) {
            if (typeof window.getSelection == "undefined") return; //IE8 or earlier...

            var body_element = document.getElementsByTagName('body')[0];
            var selection = window.getSelection();

            //if the selection is short let's not annoy our users
            if (("" + selection).length < 30) return;

            //create a div outside of the visible area
            var newdiv = document.createElement('div');
            newdiv.style.position = 'absolute';
            newdiv.style.left = '-99999px';
            body_element.appendChild(newdiv);
            newdiv.appendChild(selection.getRangeAt(0).cloneContents());

            //we need a <pre> tag workaround
            //otherwise the text inside "pre" loses all the line breaks!
            if (selection.getRangeAt(0).commonAncestorContainer.nodeName == "PRE") {
                newdiv.innerHTML = "<pre>" + newdiv.innerHTML + "</pre>";
            }

            newdiv.innerHTML += "<br />Find all the best home products at &copy; <a href='"
                + document.location.href + "'>NeedPlumbingSupplies.com</a>";

            selection.selectAllChildren(newdiv);
            window.setTimeout(function () { body_element.removeChild(newdiv); }, 200);
        });
    } else {
        console.log( "Internal User: "+$("#user-ip").data('uip') );
    }
    
    //GET PAGE WIDTH AND HEIGHT FOR USE
    var wWidth = $( window ).width();
    var wHeight = $( window ).height();

    //CONTENT BOX DIMENSION FIX AND NORMALIZE HEIGHT
    fixTwoColLayout(wWidth);
    $(window).resize(function(){fixTwoColLayout(wWidth);}); 
});