<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
$import = new ImportStartvolt();
$error = '';

if (!empty($_POST['send_form_images'])) {
    $file = $_FILES['import_images'];
    $check = CFile::CheckFile($file, 0, false, "zip");
    if (strlen($check) > 0) {
        $error = $check;
    }
    else{
       $_SESSION['IMPORT_STEP'] = 0; 
       $_SESSION['IMPORT_START'] = time();
       $_SESSION['IMPORT_TIME'] = 0;
       $script_time = $import->GetTime();
       $import->image_archive_file = $file;
       $step = $import->ImportImages();
    }
    
}


if(!empty($error)){
    ?>
<script>
        parent.images_load_error('<?php echo $error; ?>');
</script>
<?php
}
else{
    $script_time = $import->GetTime();
    $description = $import->import_images_steps[$step]['description'];
    if(!$description){
        $description = '';
    }
    ?>
<script>
    data = {};
    data.description = '<?php echo $description; ?>';
    data.time = 'Выполнение: <?php echo $script_time; ?>';
    data.process = '1';
    data.step = '<?php echo $_SESSION['IMPORT_STEP']; ?>';
    parent.images_import(data);
</script>
<?
}

?>


