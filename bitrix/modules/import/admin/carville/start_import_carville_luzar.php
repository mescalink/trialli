<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/carville_model.php");
if (empty($_SESSION['IMPORT_PROCESS'])) {
    $_SESSION['IMPORT_PROCESS'] = 1;
}
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
$import = new ImportProductsLuzar();
$end = count($import->import_steps);


if ($_SESSION['IMPORT_STEP'] > $end) {
    $_SESSION['IMPORT_STEP'] = 0;
    $script_time = $import->GetTime();
    $_SESSION['IMPORT_START'] = 0;
    $_SESSION['IMPORT_TIME'] = 0;
    echo json_encode(array('process' => '0', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Закончено за ' . $script_time,'description'=>''));
    return;
} else {
    $step = $import->Import();
        if ($step > $end) {
            $_SESSION['IMPORT_STEP'] = 0;
            $script_time = $import->GetTime();
            $_SESSION['IMPORT_START'] = 0;
            $_SESSION['IMPORT_TIME'] = 0;
            echo json_encode(array('process' => '0', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Закончено за ' . $script_time,'description'=>''));
            return;
        }
        
    $_SESSION['IMPORT_STEP'] = $step;
    $script_time = $import->GetTime();
    $description = $import->import_steps[$step]['description'];
    if(!empty($import->import_steps[$step]['result']['process_step'])){
    $description = $description.'('.$import->import_steps[$step]['result']['process_step'].')';
    }
    
    if(!$description){
        $description = '';
    }
    echo json_encode(array('process' => '1', 'step' => $_SESSION['IMPORT_STEP'], 'time' => 'Выполнение: '.$script_time,'description'=>$description));
    return;
}
?>

