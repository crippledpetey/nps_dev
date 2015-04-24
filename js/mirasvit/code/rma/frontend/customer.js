

jQuery.noConflict();
var orderItemClick = function(itemId){
    jQuery("#item" + itemId).toggle();
}
// jQuery(document).ready(function($) {
//     $('#rma_submit').click(function(e) {
//         if ($("#rma-items input:checked" ).length == 0) {
//         	alert($('#error_message_no_items').html());
//             return false;
//         }
//     	return true;
//     });
// });

Validation.add('validate-rma-quantity', 'The quantity is greater then allowed.', function(v, elm) {
    var result = Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
    var reRange = new RegExp(/^digits-range-[0-9]+-[0-9]+$/);
    $w(elm.className).each(function(name, index) {
        if (name.match(reRange) && result) {
            var min = parseInt(name.split('-')[2], 10);
            var max = parseInt(name.split('-')[3], 10);
            var val = parseInt(v, 10);
            result = (v >= min) && (v <= max);
        }
    });
    return result;
});