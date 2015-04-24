jQuery(document).ready(function($){

	$("td.value select.multiselect option:selected")each(function(){
		$(this).addClass("helloClassname");
		$(this).prependTo("td.value select.multiselect");
	});
});

