jQuery(document).ready(function($){

	function clearDisplayGrid(elem){
		//clear existing clearer elements
		remvClearer(elem);

		//get number of items to show
		var availTiles = Math.floor(  $(elem).width() / 180 );

		console.log( availTiles );
		
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
	  clearDisplayGrid("ul#category-list");
	  normalizeDropContainers();
	});
	
});