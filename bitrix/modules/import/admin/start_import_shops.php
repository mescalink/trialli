<?php
session_start();
$_SESSION['IMPORT_STEP'] = 0;
$error = '';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
DeleteDirFilesEx("/bitrix/cache/import_shops/");
DeleteDirFilesEx("/upload/import_shops/tmp/");
$file = $_FILES['import_shops'];
$check = CFile::CheckFile($file, 0, false, "csv");
if (strlen($check) > 0) {
    $error = $check;
} else {
    $import = new ImportStartvolt();
    $_SESSION['IMPORT_STEP'] = 0;
    $_SESSION['IMPORT_START'] = time();
    $_SESSION['IMPORT_TIME'] = 0;
    $script_time = $import->GetTime();
    $import->shops_file = $file;
    $step = $import->ImportShops();
}
if (!empty($error)) {
    ?>
    <script>
        parent.shops_load_error('<?php echo $error; ?>');
    </script>
    <?php
} else {
    $script_time = $import->GetTime();
    $description = $import->import_shops_steps[$step]['description'];
    if (!$description) {
        $description = '';
    }
    ?>
    <script>
        data = {};
        data.description = '<?php echo $description; ?>';
        data.time = 'Выполнение: <?php echo $script_time; ?>';
        data.process = '1';
        data.step = '<?php echo $_SESSION['IMPORT_STEP']; ?>';
        parent.shops_import(data);
    </script>
    <?
}
?>
