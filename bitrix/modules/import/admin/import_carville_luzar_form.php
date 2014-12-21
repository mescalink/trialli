<h2>Импорт товаров <b><i>Luzar</i></b></h2>
<table>
    <tr>
        <td><h3>Загрузка товаров</h3></td>
        <td><input type="submit" id="start_import_luzar" onclick="import_products_luzar(0);" value="Импорт"<?php if (!$import->can_import): ?> disabled="disabled"<?php endif; ?> /></td>
        <td><span class="import-wait"><img src="/bitrix/js/main/core/images/wait.gif" /></span><span class="time">Статус: <span class="description-value"></span> <span class="time-value"></span></span></td>
    </tr>
</table>

<div class="import_products">
    <h4>Файл с продукцией</h4>
    <p><i>Содержит краткое наименование, OEM-номер, привязку к маркам и моделям автомобилей, фирменное название, url и название категории</i></p>
    <?php if (!empty($import->import_products_file)): ?>
        <p><span style="color:green;">Актуальный файл:</span></p>
        <p><span style="color:green;"><a href="<?php echo $import->import_products_file['PATH']; ?>"><?php echo $import->import_products_file['NAME']; ?> (<?php echo $import->import_products_file['DATE']; ?>)</a></span></p>
    <?php else: ?>
        <span style="color:red;">На данный момент не загружено файлов</span>
    <?php endif; ?>
    <form action="" id="file_<?php echo $import->PRODUCTS_MODULE_ID; ?>" method="POST" name="form_<?php echo $import->PRODUCTS_MODULE_ID; ?>" enctype="multipart/form-data">
        <table>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="text" name="filename_<?php echo $import->PRODUCTS_MODULE_ID; ?>" size="30" maxlength="255" value="">
                </td>
                <td>
                    <span class="adm-input-file">
                        <span>Добавить файл</span>
                        <input type="file" name="import_file" size="30" maxlength="255" value="" onchange="ChangeFileImport(this, 'filename_<?php echo $import->PRODUCTS_MODULE_ID; ?>', 'form_<?php echo $import->PRODUCTS_MODULE_ID; ?>')" class="adm-designed-file">
                    </span>
                </td>
            </tr>
            <tr>
                <td><?php if (!empty($upload_error) && $upload_error['module'] == $import->PRODUCTS_MODULE_ID): ?><?php echo $upload_error['message']; ?><?php endif; ?></td>
                <td>
                    <input type="submit" value="Обновить" />
                </td>
            </tr>
        </table>
        <input type="hidden" name="send_form" value="<?php echo $import->PRODUCTS_MODULE_ID; ?>">
    </form>
</div>

<div class="import_sections">
    <h4>Файл с розничными ценами</h4>
    <p><i>Содержит товары с розничной ценой и указанием на новинку.</i></p>
    <?php if (!empty($import->import_sections_file)): ?>
        <p><span style="color:green;">Актуальный файл:</span></p>
        <p><span style="color:green;"><a href="<?php echo $import->import_sections_file['PATH']; ?>"><?php echo $import->import_sections_file['NAME']; ?> (<?php echo $import->import_sections_file['DATE']; ?>)</a></span></p>
    <?php else: ?>
        <span style="color:red;">На данный момент не загружено файлов</span>
    <?php endif; ?>
    <form action="" id="file_<?php echo $import->SECTIONS_MODULE_ID; ?>" method="POST" name="form_<?php echo $import->SECTIONS_MODULE_ID; ?>" enctype="multipart/form-data">
        <table>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="text" name="filename_<?php echo $import->SECTIONS_MODULE_ID; ?>" size="30" maxlength="255" value="">
                </td>
                <td>
                    <span class="adm-input-file">
                        <span>Добавить файл</span>
                        <input type="file" name="import_file" size="30" maxlength="255" value="" onchange="ChangeFileImport(this, 'filename_<?php echo $import->SECTIONS_MODULE_ID; ?>', 'form_<?php echo $import->SECTIONS_MODULE_ID; ?>')" class="adm-designed-file">
                    </span>
                </td>
            </tr>
            <tr>
                <td><?php if (!empty($upload_error) && $upload_error['module'] == $import->SECTIONS_MODULE_ID): ?><?php echo $upload_error['message']; ?><?php endif; ?></td>
                <td>
                    <input type="submit" value="Обновить" />
                </td>
            </tr>
        </table>
        <input type="hidden" name="send_form" value="<?php echo $import->SECTIONS_MODULE_ID; ?>">
    </form>
</div>
