<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/carville_model.php");
$_SESSION['IMPORT_STEP'] = 0;
$_SESSION['IMPORT_START'] = 0;
$_SESSION['IMPORT_TIME'] = 0;
$_SESSION['IMPORT_STEP'] = 0;
DeleteDirFilesEx("/bitrix/cache/import_carville/");
DeleteDirFilesEx("/bitrix/cache/import_price/");