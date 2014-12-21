function import_products_trialli(step) {
    $('#start_import_trialli').attr('disabled', 'disabled');
    $('.import-wait').show();
    $('.time').find('.description-value').show();
    BX.showWait();
    data = {};
    data.step = step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/start_import_carville_trialli.php',
        data: data,
        success: function(data) {
            console.log(data);
            BX.closeWait();
            answer = jQuery.parseJSON(data);
            $('.time').show();
            $('.import-wait').show();
            $('.time').find('.time-value').text(answer.time);
            $('.time').find('.description-value').text(answer.description);
            if (answer.process == '1') {
                import_products_trialli(answer.step);
            }
            else {
                $('.time').find('.description-value').hide();
                $('.import-wait').hide();
                $('#start_import_trialli').removeAttr('disabled');
            }

        },
        error: function(xhr, str) {

        }
    });


}


