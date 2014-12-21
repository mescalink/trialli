<pre>
<?php


session_start();
$_SESSION['IMPORT_STEP'] = 4;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/carville_model.php");
//DeleteDirFilesEx("/bitrix/cache/import_images/");
//DeleteDirFilesEx("/upload/import_images/tmp/");
$import = new ImportProductsLuzar();
$end = count($import->import_steps);
$step =4;
while($step < $end){
$step = $import->Import();
}
?>
</pre>