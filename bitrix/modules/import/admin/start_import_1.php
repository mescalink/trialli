<pre>
<?php


session_start();
$_SESSION['IMPORT_STEP'] = 0;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
//DeleteDirFilesEx("/bitrix/cache/import_images/");
//DeleteDirFilesEx("/upload/import_images/tmp/");
$import = new ImportStartvolt();
$end = count($import->import_steps);
$step = 0;
while($step < $end){
$step = $import->Import();
}
?>
</pre>

