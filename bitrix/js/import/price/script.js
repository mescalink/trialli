function price_load_error(error) {
    $('#import_price_td_auto').find('.span-error-price').text(error);
    $('#import_price_carville').find('.span-error-price').text(error);
}

function price_import(data) {
    BX.closeWait();
    if (data.process != '1') {
        $('.time-price').find('.time-value-price').text(data.time);
        $('.time-price').find('.description-value-price').text(data.description);
        $('.import-wait-price').hide();
        $('.start_price_import').removeAttr('disabled');
        $('.price_import_form').get(0).reset();
        return false;
    }
    $('.start_price_import').attr('disabled', 'disabled');
    $('.import-wait-price').show();
    $('.time-price').show();
    $('.import-wait-price').show();
    $('.time-price').find('.time-value-price').text(data.time);
    $('.time-price').find('.description-value-price').text(data.description);


    BX.showWait();

    datasend = {};
    datasend.step = data.step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/import_price.php',
        data: datasend,
        success: function(data_response) {
            console.log(data_response);
            answer = jQuery.parseJSON(data_response);
            
            price_import(answer);
        },
        error: function(xhr, str) {

        }
    });


}

