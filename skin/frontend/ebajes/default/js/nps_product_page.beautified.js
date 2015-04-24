jQuery(document).ready(function(e) {
    function i(i) {
        console.log("activating option " + i);
        var a = e("a#prd-img-lnk-" + i);
        e("#active-image-holder > a").appendTo("#pending-image-holder"), a.prependTo("#active-image-holder");
    }
    function a(i) {
        if (e("#page-overlay-dark").removeClass("hidden"), e(i).find(".video-pop-close").removeClass("hidden"), 
        "" !== e(i).data("origSrc") && void 0 !== e(i).data("origSrc")) {
            var a = e(i).data("origSrc");
            e(i).children("iframe").attr("src", a);
        } else {
            var a = e(i).find("iframe").prop("src");
            e(i).attr("data-orig-src", a);
        }
        e("#page-overlay-dark").animate(400, function() {
            e(this).css({
                height: e(window).height(),
                width: "100%",
                display: "block"
            });
        }, function() {
            e(i).removeClass("hidden"), e(i).css({
                position: "fixed",
                "z-index": 1e4,
                top: "10%",
                left: (e(window).width() - e(i).outerWidth()) / 2,
                padding: "15px",
                background: "#f9f9f9",
                color: "#444",
                "text-shadow": "none",
                "-webkit-border-radius": "4px",
                "-moz-border-radius": "4px",
                "border-radius": "4px",
                "box-shadow": "0 10px 25px rgba(0, 0, 0, 0.5)"
            });
        });
    }
    e(".preselected-finish .inventory-controller select option").each(function() {
        e(this).attr("value") == e(".preselected-finish").data("finishId") ? e(this).attr("selected", !0) : e(this).removeAttr("selected");
    }), i(e(".preselected-finish .inventory-controller select option:selected").val()), 
    e(".video-pop").each(function() {
        var i = .8 * e(window).height(), a = .8 * e(window).width();
        e(this).children("iframe").attr({
            height: i,
            width: a
        });
    }), e(".video-pop-close").click(function() {
        e("#page-overlay-dark").animate(400, function() {
            e(this).css("opacity", 0);
        }, function() {
            e("#page-overlay-dark").attr("style", ""), e("#page-overlay-dark").addClass("hidden"), 
            e(".video-pop").each(function() {
                e(this).addClass("hidden").attr("style", ""), e(this).children("iframe").attr("src", ""), 
                e(".trigger-video-pop").each(function() {
                    e(this).removeClass("hidden");
                });
            });
        });
    }), e("#page-overlay-dark").click(function() {
        e(".video-pop").each(function() {
            e(this).addClass("hidden").attr("style", ""), e(this).children("iframe").attr("src", ""), 
            e(".trigger-video-pop").each(function() {
                e(this).removeClass("hidden");
            });
        });
    });
    e(window).width(), e(window).height();
    0 == e("#qty").val() && e("#qty").val("1"), e(".inventory-controller select").change(function() {
        var i = e(this).find(":selected").text(), a = e("#prd-page-availability").html();
        e(".include-loader-bkg").addClass("overridden"), i.search("OUT OF STOCK") > 0 || i.search("Out of stock") > 0 ? (e(".product-img-box .product-image span").removeClass("scream-out-in-stock"), 
        e(".product-img-box .product-image span").removeClass("scream-out-of-stock"), e(".product-img-box .product-image span").addClass("scream-out-of-stock"), 
        "in stock" == a.toLowerCase() && (e("#prd-page-availability").parent("p.availability").removeClass("in-stock"), 
        e("#prd-page-availability").parent("p.availability").addClass("out-of-stock"), e("#prd-page-availability").empty(), 
        e("#prd-page-availability").append("OUT OF STOCK"))) : (e(".product-img-box .product-image span").removeClass("scream-out-in-stock"), 
        e(".product-img-box .product-image span").removeClass("scream-out-of-stock"), e(".product-img-box .product-image span").addClass("scream-in-stock"), 
        "out of stock" == a.toLowerCase() && (e("#prd-page-availability").parent("p.availability").addClass("in-stock"), 
        e("#prd-page-availability").parent("p.availability").removeClass("out-of-stock"), 
        e("#prd-page-availability").empty(), e("#prd-page-availability").append("IN STOCK")));
    }), e(window).resize(function() {
        e(window).width(), e(window).height();
    }), e(".attachment-icon a").each(function() {
        e(this).mouseover(function() {
            var i = e(this).children("img").attr("src").match(/[^\.]+/) + "_hover.png";
            e(this).children("img").attr("src", i);
        }), e(this).mouseout(function() {
            e(this).children("img").animate(100, function() {
                var i = e(this).attr("src").replace("_hover.png", ".png");
                e(this).attr("src", i);
            });
        });
    }), e(".trigger-video-pop").click(function() {
        e(this).addClass("hidden");
        var i = e(this).data("triggerIdKey") + "-video-pop";
        a(e("#" + i));
    }), e(document).keyup(function(i) {
        27 == i.keyCode && (console.log(e(".video-stream")), e(".video-stream").stopVideo());
    });
});