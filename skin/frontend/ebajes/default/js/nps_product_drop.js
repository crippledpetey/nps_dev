jQuery(document).ready(function($){

	function clearDisplayGrid(elem){
		//clear existing clearer elements
		remvClearer(elem);

		//get number of items to show
		var tileMargin = $("ul.products-grid > li:first-child").css("margin-right");
		tileMargin = parseInt(tileMargin);
		var availTiles = Math.floor(  $(elem).width() / ( $("ul.products-grid > li:first-child").outerWidth() + tileMargin ) );
		
		//set start for counting iterations
		var i = 1;

		//cycle through each product item
		$("ul.products-grid").children("li").each(function(){
			//check if element is third
			if( i == availTiles ){
				//add clearing block if so
				addClearer( $(this) );
				i=1;
			} else {
				//if not increase count and continue
				i++;
			}
		});
	}

	function addClearer(elem){
		$(elem).after("<div class='clearer'></div>");
	}
	function remvClearer(elem){
		//test if next element is clearer
		$(elem).children("div.clearer").remove();
	}

	function normalizeDropContainers(){
		if( $(window).width() > 740 ){
			var mainWidth = $(window).width() - 430;
			$(".col-wrapper > .col-main").css("width",mainWidth);
		} else {
			$(".col-wrapper > .col-main").css("width","");
		}
	}

	//normalize the grid container
	normalizeDropContainers();

	//clear the grid on page load
	clearDisplayGrid("ul.products-grid");


	///re-run normalize on widow rezise
	$( window ).resize(function() {
	  clearDisplayGrid("ul.products-grid");
	  normalizeDropContainers();
	});

	//make sure all help links open in a new window
	$('.layer-helper-content a').attr('target', '_blank');

	//display layer nav help content on hover
	$(".layer-helper-toggle").click(function(){

		var offset = $(this).offset();
		$(this).siblings(".layer-helper-content").addClass("active");
		if( $(window).width() > 740 ){
			var leftVal = offset.left + 30;
		} else {
			var leftVal = offset.left - 75;
		}
		$(this).siblings(".layer-helper-content").css({"top": offset.top - 10, 'left': leftVal });
	});

	//close dialogue on click of close button
	$(".layer-helper-content > .close-content-helper").click(function(){
		$(this).parent().removeClass("active");
	});

	//hide all escapable overlays on escape key press
    $(document).keyup(function(e) {
        //if escape key is pressed
        if (e.keyCode == 27) { 
            $(".active.escapable").removeClass("active");
            $(".grid-short-description").each(function(){
            	$(this).slideUp(400,function(){
					$(this).siblings(".grid-short-desc-toggle").empty();
					$(this).siblings(".grid-short-desc-toggle").html("&#x25BC;");
				});		
            });
        }  
    });


    $("#price-number-input-low").change(function(){
	    var hiValue = parseInt( $("#price-number-input-low").val() ) + 25;
	    if( $("#price-number-input-hi").val() < hiValue ){
	        $("#price-number-input-hi").val( hiValue );
	    }
	});
	$("#price-number-input-hi").change(function(){
	    if( $(this).val() < parseInt( $("#price-number-input-low").val() ) + 25 ){
	        $("#price-number-input-low").val( $(this).val() - 25 );
	    }
	});

	$("#price-range-number-input-apply").click(function(){
		//set vars
        var priceLow = $("#price-number-input-low").val();
        var priceHi = $("#price-number-input-hi").val();
        var redir = $(this).data('url')+"price="+priceLow+'-'+priceHi;
        window.location.replace(redir);
	});


	/* SHORT DESCRIPTION HOVER FUNCTION */
	$(".grid-short-desc-toggle").hover(function(){

		$(this).siblings(".grid-short-description").slideDown(600, function(){
			$(this).siblings(".grid-short-desc-toggle").empty();
			$(this).siblings(".grid-short-desc-toggle").html("&#x25B2;");
		});	
	});
	
	$(".grid-short-description > .close-shortdesc").click( function(){
		$(this).parent(".grid-short-description").slideUp(400,function(){
			$(this).siblings(".grid-short-desc-toggle").empty();
			$(this).siblings(".grid-short-desc-toggle").html("&#x25BC;");
		});		
	});

	/* SHORT DESCRIPTION HOVER FUNCTION */
	$(".recent-desc-toggle").click(function(){
		if( $(this).parent(".recent-prd-desc").hasClass("active") ){

			$(this).parent(".recent-prd-desc").removeClass("active");
			$(this).siblings(".rcnt-prd-desc-bdy").slideUp(300, function(){
				$(this).siblings(".recent-desc-toggle").removeClass("show-helper");
				$(this).siblings(".recent-desc-toggle").empty();
				$(this).siblings(".recent-desc-toggle").html("READ MORE");
			});

		} else {

			$(this).parent(".recent-prd-desc").addClass("active");
			$(this).siblings(".rcnt-prd-desc-bdy").slideDown(450, function(){
				$(this).siblings(".recent-desc-toggle").addClass("show-helper");
				$(this).siblings(".recent-desc-toggle").empty();
				$(this).siblings(".recent-desc-toggle").html("&#x25B2;");
			});	
		}
	});

	/* ADD SCROLL BARS TO LONG LAYER NAV SECTIONS */
	$("#narrow-by-list dd").each(function(){
		console.log($(this).height());
		if( $(this).height() > 200 ){
			$(this).css({
				"height"	 	: 200,
				"overflow-y"	: "scroll",
			});
		}
	});

});