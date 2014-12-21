<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/import/classes/model.php");
$_SESSION['IMPORT_STEP'] = 0;
$_SESSION['IMPORT_START'] = 0;
$_SESSION['IMPORT_TIME'] = 0;
$_SESSION['IMPORT_STEP'] = 0;
DeleteDirFilesEx("/bitrix/cache/import_images/");
DeleteDirFilesEx("/bitrix/cache/import/");
DeleteDirFilesEx("/bitrix/cache/import_shops/");
DeleteDirFilesEx("/upload/import_images/tmp/");
DeleteDirFilesEx("/upload/import_shops/tmp/");

