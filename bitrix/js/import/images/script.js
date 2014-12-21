function images_load_error(error) {
    $('#import_images').find('.span-error-image').text(error);
}

function images_import(data) {
    BX.closeWait();
    if (data.process != '1') {
        $('.time-image').find('.time-value-image').text(data.time);
        $('.time-image').find('.description-value-image').text(data.description);
        $('.import-wait-image').hide();
        $('#start_image_import').removeAttr('disabled');
        $('#import_images').get(0).reset();
        return false;
    }
    $('#start_image_import').attr('disabled', 'disabled');
    $('.import-wait-image').show();
    $('.time-image').show();
    $('.import-wait-image').show();
    $('.time-image').find('.time-value-image').text(data.time);
    $('.time-image').find('.description-value-image').text(data.description);


    BX.showWait();

    datasend = {};
    datasend.step = data.step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/import_images.php',
        data: datasend,
        success: function(data_response) {
            //console.log(data_response);
            answer = jQuery.parseJSON(data_response);

            images_import(answer);
        },
        error: function(xhr, str) {

        }
    });
}

function images_import_carville(data) {
    BX.closeWait();
    if (data.process != '1') {
        $('.time-image').find('.time-value-image').text(data.time);
        $('.time-image').find('.description-value-image').text(data.description);
        $('.import-wait-image').hide();
        $('#start_image_import').removeAttr('disabled');
        $('#import_images').get(0).reset();
        return false;
    }
    $('#start_image_import').attr('disabled', 'disabled');
    $('.import-wait-image').show();
    $('.time-image').show();
    $('.import-wait-image').show();
    $('.time-image').find('.time-value-image').text(data.time);
    $('.time-image').find('.description-value-image').text(data.description);


    BX.showWait();

    datasend = {};
    datasend.step = data.step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/import_images_carville.php',
        data: datasend,
        success: function(data_response) {
            console.log(data_response);
            answer = jQuery.parseJSON(data_response);

            images_import_carville(answer);
        },
        error: function(xhr, str) {

        }
    });
}



