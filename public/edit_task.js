jQuery(document).ready(function($) {
    var isMarketOnClick = function () {
        var $thisObj = $('#is_market');
        if ($thisObj.prop('checked')) {
            $('#check_for_update').prop('checked', true).prop('readonly', true);
            $('.tab-pane#market').find('input, select').prop('disabled', false);
            $('.nav-tabs a[href="#market"]').closest('li').show();
        } else {
            $('#check_for_update').prop('readonly', false);
            $('.tab-pane#market').find('input, select').prop('disabled', true);
            $('.nav-tabs a[href="#market"]').closest('li').hide();
            $('.nav-tabs li:eq(0) a').tab('show');
        }
    }

    var profileIdOnChange = function () {
        var $thisObj = $('#profile_id');
        var $option = $('option:selected', $thisObj);
        var network = $option.attr('data-network');
        if ((network == 'Vk') || (network == 'Facebook')) {
            $('#is_market').prop('disabled', false).closest('.control-group').show();
            isMarketOnClick();
        } else {
            $('#is_market').prop('checked', false).prop('disabled', true).closest('.control-group').hide();
            isMarketOnClick();
        }
    }

    $('#profile_id').on('change', function () {
        profileIdOnChange();
        $('#group_id').RAAS_getSelect(
            'ajax.php?p=cms&m=social&sub=dev&action=groups&id=' + $(this).val(), 
            {
                before: function(data) { 
                    return data.Set; 
                }
            }
        );
    });

    $('[data-role="upload-counter"]').closest('.form-horizontal').each(function () {
        if ($('.controls', this).length > 1) {
            $('.controls', this).each(function () {
                $(this).append('<a href="#" data-role="raas-list-move"><i class="icon icon-resize-vertical"></i></a>');
            });
        }
        $(this).sortable({ axis: 'y', 'handle': '[data-role="raas-list-move"]', containment: $(this) });
    });

    $('#is_market').on('click', function () {
        isMarketOnClick();
        if ($(this).prop('checked')) {
            $('#category_id').RAAS_getSelect(
                'ajax.php?p=cms&m=social&sub=dev&action=categories&id=' + $('#profile_id').val(), 
                {
                    before: function(data) { 
                        return data.Set; 
                    },
                    after: function (data) {
                        $('option[data-style]', this).each(function () {
                            $(this).attr('style', $(this).attr('data-style'));
                        });
                        $('option[data-disabled]', this).each(function () {
                            $(this).attr('disabled', $(this).attr('data-disabled'));
                        });
                        $('option:not(:disabled):eq(0)', this).prop('selected', true);
                    }
                }
            );
        }
    });


    profileIdOnChange();
    isMarketOnClick();
});