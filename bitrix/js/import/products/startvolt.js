function ChangeFileImport(input, field, form_name) {
    str_file = input.value;
    str_file = str_file.replace(/\\/g, '/');
    filename = str_file.substr(str_file.lastIndexOf("/") + 1);
    form = document[form_name];
    form[field].value = filename;
}

function import_products_startvolt(step) {
    $('#start_import_startvolt').attr('disabled', 'disabled');
    $('.import-wait').show();
    $('.time').find('.description-value').show();
    BX.showWait();
    data = {};
    data.step = step;
    $.ajax({
        type: 'POST',
        url: '/bitrix/tools/import/start_import_carville_startvolt.php',
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
                import_products_startvolt(answer.step);
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


