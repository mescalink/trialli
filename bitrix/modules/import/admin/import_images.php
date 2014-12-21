<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
if (empty($_SESSION['IMPORT_STEP'])) {
    $_SESSION['IMPORT_STEP'] = 0;
}
if (empty($_SESSION['IMPORT_START'])) {
    $_SESSION['IMPORT_START'] = time();
}
if (empty($_SESSION['IMPORT_TIME'])) {
    $_SESSION['IMPORT_TIME'] = 0;
}
if (!empty($_POST['step'])) {
    $_SESSION['IMPORT_STEP'] = $_POST['step'];
} else {
    $_SESSION['IMPORT_STEP'] = 0;
}
$import = new ImportStartvolt();
$end = count($import->import_images_steps);
if ($_SESSION['IMPORT_STEP'] > $end) {
    $_SESSION['IMPORT_STEP'] = 0;
    $script_time = $import->GetTime();
    $_SESSION['IMPORT_START'] = 0;
    $_SESSION['IMPORT_TIME'] = 0;
    DeleteDirFilesEx("/bitrix/cache/import_images/");
    DeleteDirFilesEx("/upload/import_images/tmp/");
    echo json_encode(array('process' => '0', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Закончено за ' . $script_time, 'description' => ''));
    return;
} else {
    $step = $import->ImportImages();
    if ($step === false) {
        $_SESSION['IMPORT_STEP'] = 0;
        $script_time = $import->GetTime();
        $_SESSION['IMPORT_START'] = 0;
        $_SESSION['IMPORT_TIME'] = 0;
        DeleteDirFilesEx("/bitrix/cache/import_images/");
        DeleteDirFilesEx("/upload/import_images/tmp/");
        echo json_encode(array('process' => '0', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Закончено за ' . $script_time, 'description' => (!empty($import->import_error)?$import->import_error:'')));
        return;
    }


    if ($step > $end) {
        $_SESSION['IMPORT_STEP'] = 0;
        $script_time = $import->GetTime();
        $_SESSION['IMPORT_START'] = 0;
        $_SESSION['IMPORT_TIME'] = 0;
        DeleteDirFilesEx("/bitrix/cache/import_images/");
        DeleteDirFilesEx("/upload/import_images/tmp/");
        echo json_encode(array('process' => '0', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Закончено за ' . $script_time, 'description' => ''));
        return;
    }

    $_SESSION['IMPORT_STEP'] = $step;
    $script_time = $import->GetTime();
    $description = $import->import_images_steps[$step]['description'];
    if (!$description) {
        $description = '';
    }
    echo json_encode(array('process' => '1', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Выполнение: ' . $script_time, 'description' => $description));
    return;
}
?>
