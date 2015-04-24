jQuery.noConflict();
jQuery(document).ready(function($) {
    var el   = $('#reply');
    var f    = $('#is_internal');
    var note = $('#rma_reply_note');

    var updateSaveBtn = function () {
        if ($('#reply').val() == '') {
            $('.saveRmaBtn').html('<span>Update</span>');
            $('.saveAndContinueRmaBtn').html('<span>Update And Continue Edit</span>');
        } else {
            $('.saveRmaBtn').html('<span> Update And Send Message </span>');
            $('.saveAndContinueRmaBtn').html('<span> Send And Continue Edit </span>');
        }
    }

    $('#reply_type').change(function() {
        var type = $('#reply_type').val();
        el.removeClass('internal');
        if (type == 'public') {
            note.html('');
        } else if (type == 'internal') {
            el.addClass('internal');
            note.html('Only store managers will see this message');
        }
    });
    $('#public_reply_btn').click(function() {
        el.removeClass('internal');
        $('#public_reply_btn').addClass('active');
        $('#internal_reply_btn').removeClass('active');
        f.val(0);
        note.html('');
        updateSaveBtn();
    });

    $('#internal_reply_btn').click(function() {
        el.addClass('internal');
        $('#public_reply_btn').removeClass('active');
        $('#internal_reply_btn').addClass('active');
        f.val(1);
        note.html('Only store managers will see this message');
        updateSaveBtn();
    });

    $('#reply').keyup(function() {
        updateSaveBtn();
    });

    $('#template_id').change(function() {
        var id = $('#template_id').val();
        if (id != 0) {
            template = $('#htmltemplate-' + id).html();
            var val = $('#reply').val();
            if (val != '') {
                val = val + '\n';
            }
            $('#reply').val(val + template);
            $('#template_id').val(0);
            updateSaveBtn();
        }
    });
});