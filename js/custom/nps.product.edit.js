jQuery(document).ready(function($){
	function checkNameLength(){
		var manuLen = $("#manufacturer option:selected").text().length;
		var skuLen = $("#sku").val().length;
		var nameLen = $("input#name").val().length;

		//name+manufacturer+sku+separator
		if( ( manuLen + skuLen + nameLen + 3 ) >= 135 ){
			//check if parent already has notifier
			if( !$("input#name").siblings(".nps-product-edit-popup-helper").length ){
				$("input#name").parent("td").append("<span class='nps-product-edit-popup-helper'><span>");	
			}
			//change notifier text
			$("input#name").siblings(".nps-product-edit-popup-helper").empty();
			$("input#name").siblings(".nps-product-edit-popup-helper").text( ( manuLen + skuLen + nameLen + 3 ) - 134 +" OVER" );
			
			$("input#name").css({"box-shadow":"0 0 10px red"});
		} else {
			$("input#name").siblings(".nps-product-edit-popup-helper").remove();
			$("input#name").css({"box-shadow":"none"});
		}
	}
	function checkForChange(){
		//if the inputs haven't already been hidden
		if( $(".nps-change-notify").first().hasClass("hidden") ){
			//set default to false
			var change = false;
			$("#product_info_tabs > li > a").each(function(){
				//check each tab for the change trigger
				if($(this).hasClass("changed")){
					if($(this).attr("id") !== "product_info_tabs_mediamanager"){
						//set indication to true
						change = true;
					} 
				}
			});	
			if(change){
				
				//edit inputs and messages
				$(".nps-change-notify").each(function(){
					$(this).removeClass("hidden");
				});
				$(".nps-change-dependent").each(function(){
					$(this).prop("disabled",true);
				});
			} 
		} 
	}
	//move selected attributes to the top of the list
	$("td.value select.multiselect option:selected").each(function(){
		$(this).addClass("helloClassname");
		$(this).prependTo($(this).parent("select"));
	});
	//make sure product name doesn't run into the price on product drop pages
	checkNameLength();
	$("input#name").keyup(function(){
		checkNameLength();
	});
	//activate update trigger toggle on media manager forms
	$(".nps-gallery-image-toggle").keyup(function(){
		$(this).siblings(".nps-gallery-image-update").val("true");
	});

	//on any keypress or mouse click
	$(document).keyup(function(){
		checkForChange();
	});
	$(document).click(function(){
		checkForChange();
	});

	//import mage image controller
	$("#nps-mage-image-import-button").click(function(){
		event.preventDefault();
		$(this).addClass("disabled");
		$(this).prop("disabled","disabled");
		$("#nps-mage-image-import-trigger").val("true");
		$("#nps-mage-image-import-msg").empty();
		$("#nps-mage-image-import-msg").text("The magento image will be imported on save");
		$("#nps-mage-image-import-msg").parents(".notice-msg").removeClass("hidden");
	});
});

