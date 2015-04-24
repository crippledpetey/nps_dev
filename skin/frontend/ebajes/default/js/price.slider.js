
//http://refreshless.com/nouislider/slider-values/
jQuery(document).ready(function($){
    
    
    $("#price-range-input").noUiSlider({
        start: [<?php echo $low_val ?>, <?php echo $hi_val ?>],
        margin: <?php echo $minimum_variance ?>,
        range: {'min': <?php echo $low_min ?>,'max': <?php echo $hi_max ?>},
        behaviour: 'drag',
    });
    $("#price-range-input").Link('lower').to('-inline-<div class="price-tooltip"></div>', function ( value ) {
        var frmt = value.substring(0, value.length - 3);
        $(this).html('<span>$' + frmt + '</span>');
    });
    //$("#price-range-input").Link('lower').to($("#price-number-input-low"));

    $("#price-range-input").Link('upper').to('-inline-<div class="price-tooltip"></div>', function ( value ) {
        var frmt = value.substring(0, value.length - 3);
        $(this).html('<span>$' + frmt + '</span>');
    });
    //$("#price-range-input").Link('upper').to($("#price-number-input-hi"));

});