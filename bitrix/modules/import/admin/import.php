<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
// подключим языковой файл
IncludeModuleLangFile(__FILE__);


// и прикрепим его к списку
//$lAdmin->AddAdminContextMenu($aContext);
// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //
// альтернативный вывод
// установим заголовок страницы
$APPLICATION->SetTitle('Импорт данных');

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/carville_model.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
global $USER;
if ($USER->IsAdmin()) {
    $import = new ImportProductsLuzar();
    $import_startvolt = new ImportStartvolt();
    if (!empty($_POST['send_form'])) {
        $upload_error = array();
        $file = $_FILES['import_file'];
        $info = Array("MODULE_ID" => $_POST['send_form']);
        $res = CFile::CheckFile($file, 0, false, "csv");
        if (strlen($res) > 0) {
            $upload_error = array(
                'module' => $_POST['send_form'],
                'message' => '<span class="span-error">' . $res . '</span><br>'
            );
        } else {
            $arFILE = array_merge($file, $info);
            $import->MoveFileToLog($_POST['send_form']);
            $fid = CFile::SaveFile($arFILE, $_POST['send_form']);
        }
    }

    $import->CheckImportFile();


    $aTabs = array(
        array("DIV" => "products",
            "TAB" => 'Товары',
            "ICON" => "main_user_edit",
            "ONSELECT" => 'SwitchTabs("products")',
            "TITLE" => ''),
        array("DIV" => "images",
            "TAB" => 'Картинки',
            "ICON" => "main_user_edit",
            "ONSELECT" => 'SwitchTabs("images")',
            "TITLE" => ''),
        array("DIV" => "shops",
            "TAB" => 'Точки продаж',
            "ICON" => "main_user_edit",
            "ONSELECT" => 'SwitchTabs("shops")',
            "TITLE" => ''),
        array("DIV" => "cache",
            "TAB" => 'Сброс кеша',
            "ICON" => "main_user_edit",
            "ONSELECT" => 'SwitchTabs("cache")',
            "TITLE" => ''),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    global $APPLICATION;
    $APPLICATION->AddHeadScript('/bitrix/js/import/jquery-1.8.3.min.js');
    $APPLICATION->AddHeadScript('/bitrix/js/import/script.js');
    $APPLICATION->AddHeadScript('/bitrix/js/import/products/luzar.js');
} else {
    die;
}
?>
<?
CModule::IncludeModule("iblock");
?>
<?php $tabControl->Begin(); ?>
<style>
    .tab{
        padding: 15px 0px 0px 30px;
    }
    .span-error,.span-error-image,.span-error-shops{
        color:red;
        width: 223px;
        display: inline-block;
    }
    .time,.time-image,.time-shops{
        display:none;
        color:green;
    }
    .import-wait,.import-wait-image,.import-wait-shops{
        display:none;
    }
    
</style>

<script>
    $(document).ready(function() {
        $('#import_images').find('input[type=submit]').click(function() {
            $('#import_images').find('.span-error-image').text('');
        });
        
        $('.clear-cache-button').click(function(){
            datasend = {};
            $.ajax({
            type: 'POST',
            url: '<?php echo $import_startvolt->clear_cache_path; ?>',
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
            },
            error: function(xhr, str) {

            }
        });
            
        });
        

    });
    function images_load_error(error) {
        $('#import_images').find('.span-error-image').text(error);
    }
    function shops_load_error(error) {
        $('#import_shops').find('.span-error-shops').text(error);
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
            url: '<?php echo $import_startvolt->images_import_path; ?>',
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
    function shops_import(data) {
    console.log(data);
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
            url: '<?php echo $import_startvolt->shops_import_path; ?>',
            data: datasend,
            success: function(data_response) {
                console.log(data_response);
                answer = jQuery.parseJSON(data_response);
                
                shops_import(answer);
            },
            error: function(xhr, str) {

            }
        });


    }

    function SwitchTabs(tab) {
        div = $('div[data-tab="' + tab + '"]');
        if (div.css('display') == 'none') {
            $('div.tab').hide();
            div.show();
        }
        $('.clear-cache').find('.time').text('').hide();
    }


    function ChangeFileImport(input, field, form_name) {
        str_file = input.value;
        str_file = str_file.replace(/\\/g, '/');
        filename = str_file.substr(str_file.lastIndexOf("/") + 1);
        form = document[form_name];
        form[field].value = filename;
    }
   /* function import_products(step) {
        $('#start_import').attr('disabled', 'disabled');
        $('.import-wait').show();
        $('.time').find('.description-value').show();
        BX.showWait();
        data = {};
        data.step = step;
        $.ajax({
            type: 'POST',
            url: '<?php echo $import_startvolt->start_import_path; ?>',
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
                    import_products(answer.step);
                }
                else {
                    $('.time').find('.description-value').hide();
                    $('.import-wait').hide();
                    $('#start_import').removeAttr('disabled');
                }

            },
            error: function(xhr, str) {

            }
        });


    }*/


</script>
<div class="tab" data-tab="products">
    <?php include 'import_carville_luzar_form.php'; ?>
</div>

<div class="tab" data-tab="images" style="display:none;">
    <h2>Загрузка картинок</h2>
    <p><i>Загрузка картинок архивом *zip. Названия картинок должны содержать оригинальный <b>код «СтартВОЛЬТ»</b></i></p>
    <form action="/bitrix/tools/import/start_import_images.php" id="import_images" method="POST" name="form_import_images" enctype="multipart/form-data" target="import_images_frame">
        <table>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="text" name="filename_import_images" size="30" maxlength="255" value="">
                </td>
                <td>
                    <span class="adm-input-file">
                        <span>Добавить файл</span>
                        <input type="file" name="import_images" size="30" maxlength="255" value="" onchange="ChangeFileImport(this, 'filename_import_images', 'form_import_images')" class="adm-designed-file">
                    </span>
                </td>
                <td>
                    <span class="import-wait-image">
                        <img src="/bitrix/js/main/core/images/wait.gif" />
                    </span>
                    <span class="time-image">Статус: <span class="description-value-image"></span> <span class="time-value-image"></span></span>
                </td>
            </tr>

            <tr>
                <td><span class="span-error-image"></span></td>
                <td>
                    <input type="submit" id="start_image_import" value="Загрузить" />
                </td>
                <td></td>
            </tr>
        </table>
        <input type="hidden" name="send_form_images" value="images">
    </form>
    <iframe id="import_images_frame" style="display:none;" name="import_images_frame"></iframe>
</div>
<div class="tab" data-tab="shops" style="display:none;">
    <h3>Файл с точками продаж</h3>
    <form action="/bitrix/tools/import/start_import_shops.php" id="import_shops" method="POST" name="form_import_shops" enctype="multipart/form-data" target="import_shops_frame">
        <table>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="text" name="filename_import_shops" size="30" maxlength="255" value="">
                </td>
                <td>
                    <span class="adm-input-file">
                        <span>Добавить файл</span>
                        <input type="file" name="import_shops" size="30" maxlength="255" value="" onchange="ChangeFileImport(this, 'filename_import_shops', 'form_import_shops')" class="adm-designed-file">
                    </span>
                </td>
                <td>
                    <span class="import-wait-shops">
                        <img src="/bitrix/js/main/core/images/wait.gif" />
                    </span>
                    <span class="time-shops">Статус: <span class="description-value-shops"></span> <span class="time-value-shops"></span></span>
                </td>
            </tr>

            <tr>
                <td><span class="span-error-shops"></span></td>
                <td>
                    <input type="submit" id="start_shops_import" value="Загрузить" />
                </td>
                <td></td>
            </tr>
        </table>
        <input type="hidden" name="send_form_images" value="images">
    </form>
    <iframe id="import_shops_frame" style="display:none;" name="import_shops_frame"></iframe>
</div>
<div class="tab" data-tab="cache" style="display:none;">
    <h2>Сброс кеша</h2>
    <p><i>полностью сбрасывает кеш импорта</i></p>
    <table class="clear-cache">
        <tr>
            <td><input type="button" class="clear-cache-button" value="Сбросить кеш"/></td>
            <td><span class="time">Статус: <span class="description-value"></span></span></td>
        </tr>
    </table>

</div>
<?php $tabControl->End(); ?>



<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

