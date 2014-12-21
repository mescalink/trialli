$(document).ready(function() {
    
    if(location.hash != ''){
        var hash = location.hash;
        tab = hash.replace("#","");
        $('#tab_cont_' + tab).trigger('click');
    }
    
    $('#import_images').find('input[type=submit]').click(function() {
        $('#import_images').find('.span-error-image').text('');
    });

    $('.clear-cache-button').click(function() {
        datasend = {};
        $.ajax({
            type: 'POST',
            url: '/bitrix/tools/import/clear_import_carville_cache.php',
            data: datasend,
            success: function(data_response) {
                $('.clear-cache').find('.time').text('Кеш успешно сброшен!').show();
                $('.time-image').find('.time-value-image').text('');
                $('.time-image').find('.description-value-image').text('');
                $('.import-wait-image').hide();
                $('#start_image_import').removeAttr('disabled');
                $('#import_images').get(0).reset();
                $('.time').find('.description-value').hide();
                $('.import-wait').hide();
                $('#start_import').removeAttr('disabled');
                $('#start_price_carville_import').removeAttr('disabled');
                $('#start_price_td_auto_import').removeAttr('disabled');
                $('#import_price_carville').get(0).reset();
                $('#import_price_td_auto').get(0).reset();
                $('.import-wait-price').hide();
                
            },
            error: function(xhr, str) {

            }
        });

    });

    $('.sub-tab-li').click(function() {

        tab = $(this).data('tab');
        $('.sub-tab-li').removeClass('active');
        $(this).addClass('active');
        div = $('div[data-tab="' + tab + '"]');
        if (div.css('display') == 'none') {
            $('div.sub-tab').hide();
            div.show();
        }
        $('.clear-cache').find('.time').text('').hide();
    });




});

function SwitchTabs(tab) {
    div = $('div[data-tab="' + tab + '"]');
    location.hash = tab;
    if (div.css('display') == 'none') {
        $('div.tab').hide();
        div.show();
        div.find('.sub-tab-li:first').trigger('click');
    }
    $('.clear-cache').find('.time').text('').hide();
}

