jQuery(document).ready(function(e) {
	e("#unassigned-attr-search").keyup(function() {
        var t = e("#unassigned-attr-search").val();
        e("#tree-div2 > ul > div > li").each(function() {
            var a = e(this).find("a > span").text();
            a.toLowerCase().search(t.toLowerCase()) < 0 ? e(this).closest("li").addClass("hidden") : e(this).closest("li").removeClass("hidden")
        })
    }), e("#clear-search-undefiined-attributes").click(function() {
        e("#unassigned-attr-search").val(""), e("#tree-div2 > ul > div > li").each(function() {
            e(this).removeClass("hidden")
        });
    });
    e("#product_info_tabs_categories").click(function(){
    	e("#product_info_tabs_categories_content > entry-edit-head > h4").after('<button id="clear-search-undefiined-attributes" title="Clear Search Input" onClick="clrUnassignedAttr()" type="button" class="scalable delete" style=""><span><span><span>Clear</span></span></span></button>');
    });
    e("#product_info_tabs_categories").addClass("TESTING");
    


    e(".x-tree-node-collapsed").each(function(){
    	e(this).removeClass("x-tree-node-collapsed");
    	e(this).addClass("x-tree-node-expanded");
    });

    e( document ).ajaxComplete(function() {
	  console.log( "Triggered ajaxComplete handler." );
	});
    var content = 'Find Attribute: <input type="text" id="unassigned-attr-search" onkeyup="srchUnassignedAttr()"><span style="margin-left: 25px;"><button id="clear-search-undefiined-attributes" title="Clear Search Input" onClick="clrUnassignedAttr()" type="button" class="scalable delete" style=""><span><span><span>Clear</span></span></span></button>';

    $("#page:main-container > table td:nth-child(3)").prepend( content );
    
});

(function($) {
    var content = 'Find Attribute: <input type="text" id="unassigned-attr-search" onkeyup="srchUnassignedAttr()"><span style="margin-left: 25px;"><button id="clear-search-undefiined-attributes" title="Clear Search Input" onClick="clrUnassignedAttr()" type="button" class="scalable delete" style=""><span><span><span>Clear</span></span></span></button>';

    $("#tree-div2").prepend( content );
    /*
	theParent = document.getElementById("tree-div2");
	theKid = document.createElement("span");
	theKid.innerHTML = 'Find Attribute: <input type="text" id="unassigned-attr-search" onkeyup="srchUnassignedAttr()"><span style="margin-left: 25px;"><button id="clear-search-undefiined-attributes" title="Clear Search Input" onClick="clrUnassignedAttr()" type="button" class="scalable delete" style=""><span><span><span>Clear</span></span></span></button>';
	
	// prepend theKid to the beginning of theParent
	theParent.insertBefore(theKid, theParent.firstChild);
    */

});

function srchUnassignedAttr(){
	input = document.getElementById("unassigned-attr-search").value;
	console.log( input );
}

function clrUnassignedAttr(){

}

