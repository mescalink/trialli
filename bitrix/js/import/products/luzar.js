function import_products_luzar(step) {
    $('#start_import_startvolt').attr('disabled', 'disabled');
    $('.import-wait').show();
    $('.time').find('.description-value').show();
    BX.showWait();
    data = {};
    data.step = step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/start_import_carville_luzar.php',
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
                import_products_luzar(answer.step);
            }
            else {
                $('.time').find('.description-value').hide();
                $('.import-wait').hide();
                $('#start_import_startvolt').removeAttr('disabled');
            }

        },
        error: function(xhr, str) {

        }
    });


}


