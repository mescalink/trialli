function shops_load_error(error) {
    $('#import_shops').find('.span-error-shops').text(error);
}

function shops_import(data) {
    BX.closeWait();
    if (data.process != '1') {
        $('.time-shops').find('.time-value-shops').text(data.time);
        $('.time-shops').find('.description-value-shops').text(data.description);
        $('.import-wait-shops').hide();
        $('#start_shops_import').removeAttr('disabled');
        $('#import_shops').get(0).reset();
        return false;
    }
    $('#start_shops_import').attr('disabled', 'disabled');
    $('.import-wait-shops').show();
    $('.time-shops').show();
    $('.import-wait-shops').show();
    $('.time-shops').find('.time-value-shops').text(data.time);
    $('.time-shops').find('.description-value-shops').text(data.description);


    BX.showWait();

    datasend = {};
    datasend.step = data.step;
    $.ajax({
        type: 'POST',
        url: '<?php echo $import->shops_import_path; ?>',
        data: datasend,
        success: function(data_response) {
            //console.log(data_response);
            answer = jQuery.parseJSON(data_response);

            shops_import(answer);
        },
        error: function(xhr, str) {

        }
    });


}

