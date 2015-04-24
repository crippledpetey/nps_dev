jQuery(document).ready(function($) {
	$(".standardize-children").each(function(){
		var maxHeight = 0;
		$(this).find(".std-me").each(function(){
			if( $(this).outerHeight() > maxHeight ){ maxHeight = $(this).outerHeight(); }
		});

		$(this).find(".std-me").each(function(){
			$(this).css("height",maxHeight);
		});
	});
    $("#nps_vendor_options_update input").change(function() {
    	$("#nps_vendor_options_update").find("input[name='nps_value_updated']").val("true");
    });
    $("#nps_vendor_options_update textarea").keyup(function() {
    	$("#nps_vendor_options_update").find("input[name='nps_value_updated']").val("true");
    });
    $("#nps_vendor_options_update select").change(function() {
    	$("#nps_vendor_options_update").find("input[name='nps_value_updated']").val("true");
    });
});