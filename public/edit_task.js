jQuery(document).ready(function($) {
    $('#profile_id').change(function() {
        $('#group_id').RAAS_getSelect('ajax.php?p=cms&m=social&sub=dev&action=groups&id=' + $(this).val(), {before: function(data) { return data.Set; }});
    });

    $('[data-role="upload-counter"]').closest('.form-horizontal').each(function () {
        if ($('.controls', this).length > 1) {
            $('.controls', this).each(function () {
                $(this).append('<a href="#" data-role="raas-list-move"><i class="icon icon-resize-vertical"></i></a>');
            });
        }
        $(this).sortable({ axis: 'y', 'handle': '[data-role="raas-list-move"]', containment: $(this) });
    });

});