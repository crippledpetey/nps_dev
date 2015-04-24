jQuery(document).ready(function($) {

    //OUTPUT THE PAGE AND DOCUMENT INFORMATION TO THE CONSOLE
    //console.log( $( window ) );
    //console.log( $(document) );

    //check for preselected finish
    $(".preselected-finish .inventory-controller select option").each(function() {
        if ($(this).attr('value') == $(".preselected-finish").data('finishId')) {
            $(this).attr("selected", true);
        } else {
            $(this).removeAttr("selected");
        }
    });
    /* ================== MOVE THE ACTIVE FINISH IMAGE TO THE DISPLAY PORT ================== */
    function activateFinishImage(optionId) {
        console.log(optionId);
        //changing image
        var img = $("a#prd-img-lnk-" + optionId);
        $("#active-image-holder > a").appendTo("#pending-image-holder");
        img.prependTo("#active-image-holder");
        //changing notification
        var noti = $("#prd-inv-img-noti-" + optionId);
        $(noti).siblings().addClass("hidden");
        $(noti).removeClass("hidden");
        //check qty for animate color scream
        if ($(img).data("invQty") == 0) {
            var bshadow = "rgba(255,0,0,1)";
        } else {
            var bshadow = "rgba(0,255,0,1)";
        }
        //animate the flash
        $("#active-image-holder").animate({
            boxShadow: "0 0 40px " + bshadow
        }, function() {
            $("#active-image-holder").animate({
                boxShadow: "none"
            });
        });
    }

    function activateFinishTitle(optionId) {
            if (optionId == typeof undefined || optionId == '') {
                var t = $("#default-page-title").text();
            } else {
                var t = $("#page-title-" + optionId).text();
            }
            $("#product-page-title").empty();
            $("#product-page-title").text(t);
            $(".product-title").empty();
            $(".product-title").text(t);
        }
        /* ================== FIX PRODUCT PAGE CONTENT BOXES TO NORMALIZE HEIGHT AND / OR WIDTH ================== */
    function fixPrdContentBoxDimensions(windowWidth) {

            //VERIFY THAT THE WINDOW WIDTH IS NOT A PORTRAIT MOBILE DEVICE
            if (windowWidth > 479) {

                //COLLECT THE PRODUCT ADD TO CART BOX HEIGHT
                boxHeight = $(".product-options-bottom").outerHeight() + 31;
                //boxHeight = boxHeight + parseInt($(".product-options-bottom").css('padding-top')) + parseInt($(".product-options-bottom").css('padding-bottom')) + 3;

                //MAKE SURE THAT IT IS LARGER THAN THE CURRENT SIZE OF THE OPTIONS WRAPPER
                if (boxHeight > $("#product-options-wrapper").height()) {
                    $("#product-options-wrapper").css({
                        "height": (boxHeight - 1) + "px",
                    });
                } else { //IF THE ADD TO CART CONTAINER IS NOT LARGER THAN TGE PRODUCT OPTIONS
                    $("#product-options-wrapper").css({
                        "min-height": boxHeight + "px",
                        "height": "auto",
                    });
                }

                //EXTEND THE "MORE VIEWS" CONTAINER TO THE END OF THE PAGE
                //$("#product-more-views > ul").css("width",windowWidth-100);
            }
        }
        /* ================== TRIGGER A POPUP FOR ALL THE IMAGES IN THE PRODUCT BODY ================== */
    function triggerPopUp(popup) {
            $("#page-overlay-dark").removeClass("hidden");
            $(popup).find(".video-pop-close").removeClass("hidden");
            if ($(popup).data("origSrc") !== "" && $(popup).data("origSrc") !== undefined) {
                var origSrc = $(popup).data("origSrc");
                $(popup).children("iframe").attr("src", origSrc);
            } else {
                var origSrc = $(popup).find('iframe').prop("src");
                $(popup).attr("data-orig-src", origSrc);
            }


            $("#page-overlay-dark").animate(400, function() {
                $(this).css({
                    'height': $(window).height(),
                    'width': '100%',
                    'display': 'block',
                });
            }, function() {
                $(popup).removeClass("hidden");
                $(popup).css({
                    'position': 'fixed',
                    'z-index': 10000,
                    'top': '10%',
                    'left': ($(window).width() - $(popup).outerWidth()) / 2,
                    'padding': '15px',
                    'background': '#f9f9f9',
                    'color': '#444',
                    'text-shadow': 'none',
                    '-webkit-border-radius': '4px',
                    '-moz-border-radius': '4px',
                    'border-radius': '4px',
                    'box-shadow': '0 10px 25px rgba(0, 0, 0, 0.5)',
                });
            });
        }
        /* ================== TRIGGER REVIEW WINDOW POPUP ================== */
    function reviewWindowPopup(elem) {
        //check window height to make sure the window will fit
        if ($(window).height() < 600) {
            var h = $(window).height() - 50;
        } else {
            var h = 600;
        }

        //check width to make sure the widnow will fit
        if ($(window).width() < 450) {
            var w = $(window).width() - 20;
        } else {
            var w = 450;
        }
        newwindow = window.open($(elem).attr('href'), '', 'height=' + h + ',width=' + w);
        if (window.focus) {
            newwindow.focus()
        }
        event.preventDefault();
        return false;
    }
    if ($(".preselected-finish .inventory-controller select").length) {
        //populate selected image
        activateFinishImage($(".preselected-finish .inventory-controller select option:selected").val())
        activateFinishTitle($(".preselected-finish .inventory-controller select option:selected").val());
        //change image on finish change
        $(".preselected-finish .inventory-controller select").change(function() {
            activateFinishImage($(".preselected-finish .inventory-controller select option:selected").val());
            activateFinishTitle($(".preselected-finish .inventory-controller select option:selected").val());
        });
    };


    $(".video-pop").each(function() {
        var height = $(window).height() * .8;
        var width = $(window).width() * .8;
        $(this).children("iframe").attr({
            "height": height,
            "width": width,
        });
    });
    $(".video-pop-close").click(function() {
        $("#page-overlay-dark").animate(400, function() {
            $(this).css("opacity", 0);
        }, function() {
            $("#page-overlay-dark").attr("style", "");
            $("#page-overlay-dark").addClass("hidden");
            $(".video-pop").each(function() {
                $(this).addClass("hidden").attr("style", "");
                $(this).children("iframe").attr("src", "");
                $(".trigger-video-pop").each(function() {
                    $(this).removeClass("hidden");
                })
            });
        });
    });
    $("#page-overlay-dark").click(function() {
        $(".video-pop").each(function() {
            $(this).addClass("hidden").attr("style", "");
            $(this).children("iframe").attr("src", "");
            $(".trigger-video-pop").each(function() {
                $(this).removeClass("hidden");
            })
        });
    });

    //GET PAGE WIDTH AND HEIGHT FOR USE
    var wWidth = $(window).width();
    var wHeight = $(window).height();

    //CONTENT BOX DIMENSION FIX AND NORMALIZE HEIGHT
    //fixPrdContentBoxDimensions(wWidth);

    //CHANGE PRODUCT QUANTITY TO 1 IF CURRENT QUANTITY IS 0
    if ($("#qty").val() == 0) {
        $("#qty").val("1");
    }



    //ON A WINDOW RESOLUTION CHANGE
    $(window).resize(function() {

        //RECOLLECT THE WINDOW DIMENSIONS
        var wWidth = $(window).width();
        var wHeight = $(window).height();

        //RE-NORMALIZE PRODUCT PAGE CONTENT BLOCKS
        //fixPrdContentBoxDimensions(wWidth);
    });

    $(".attachment-icon a").each(function() {
        $(this).mouseover(function() {
            var src = $(this).children("img").attr("src").match(/[^\.]+/) + "_hover.png";
            $(this).children("img").attr("src", src);
        })
        $(this).mouseout(function() {
            $(this).children("img").animate(100, function() {
                var src = $(this).attr("src").replace("_hover.png", ".png");
                $(this).attr("src", src);
            });
        });
    });
    $(".trigger-video-pop").click(function() {
        $(this).addClass("hidden");
        var popUpID = $(this).data("triggerIdKey") + "-video-pop";
        triggerPopUp($("#" + popUpID));
    });

    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $(".video-stream").stopVideo();
        }
    });
    $('p.no-rating > a').click(function() {
        reviewWindowPopup($(this));
    });
    $("p.rating-links > a").click(function() {
        reviewWindowPopup($(this));
    });
    $("#product-pg-social-share a").click(function() {
        reviewWindowPopup($(this));
    });

});
