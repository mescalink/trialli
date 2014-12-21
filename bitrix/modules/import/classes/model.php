<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

class ImportStartvolt {

    public function __construct() {
        $this->DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $this->upload_path = '/upload/';
        $this->upload_path_shops = 'import_shops/';
        $this->upload_path_images = 'import_images/';
        $this->upload_tmp_path_images = 'tmp/images';
        $this->shops_file = array();
        $this->image_archive_file = array();
        $this->tmp_image_structure = array();
        $this->products_structure = array();
        $this->products_structure_after_find_image = array();
        $this->extracted_files = false;
        $this->import_error = '';
        $this->start_import_path = '/bitrix/tools/import/start_import.php';
        $this->images_import_path = '/bitrix/tools/import/import_images.php';
        $this->shops_import_path = '/bitrix/tools/import/import_shops.php';
        $this->clear_cache_path = '/bitrix/tools/import/clear_import_cache.php';
        $this->CARS_IBLOCK_ID = 11;
        $this->PRODUCTS_IBLOCK_ID = 6;
        $this->PRODUCTS_MODULE_ID = 'import_products';
        $this->SECTIONS_MODULE_ID = 'import_sections';
        $this->IMAGES_MODULE_ID = 'import_images';
        $this->LOG_PRODUCTS_MODULE_ID = 'import_products_log';
        $this->LOG_SECTIONS_MODULE_ID = 'import_sections_log';
        $this->import_products_file = array();
        $this->import_sections_file = array();
        $this->import_shops_file = array();
        $this->import_images_file = array();
        $this->import_shops_file_content = '';
        $this->import_products_file_content = '';
        $this->import_sections_file_content = '';
        $this->catalog_flag = '::CATALOGUE::';
        $this->header_flag = 'HEADER';
        $this->product_flag = '::PRODUCT::';
        $this->field_separator = ';';
        $this->sub_category_separator = '::';
        $this->line = "\n";
        $this->combine_code_category = 'Код';
        $this->combine_code_products = 'Фирменное наим-ние товара';
        $this->db_fields_products = array(
            'Фирменное наим-ние товара' => 'PROPERTY_STARTVOLT_CODE',
            'OEM номер' => 'PROPERTY_OEM_NUMBER',
            '№' => '',
            'Краткое наименование' => 'NAME',
            'Интервал замены' => '',
            'Система автомобиля' => '',
            'Наименование изделия' => '',
            'Описание конструкции' => '',
        );
        $this->db_fields_cars = array(
            'Марка автомобиля' => 'CATEGORY_NAME',
            'Модель автомобиля' => 'ELEMENT_NAME',
            'Двигатель' => 'PROPERTY_ENGINE',
            'AC' => 'PROPERTY_AC',
            'ABS' => 'PROPERTY_ABS',
            'УР' => 'PROPERTY_UR',
            'КПП' => 'PROPERTY_KPP',
            'Дата выпуска' => 'PROPERTY_YEAR',
        );
        $this->ignore_car = 'Для любых а/м';
        $this->db_fields_sections = array(
            'Цена' => 'PROPERTY_PRICE',
            'Новинка?' => 'PROPERTY_NEW_PROD',
            'Популярный?' => 'PROPERTY_POPULAR_PROD',
            'Наименование' => 'NAME',
            'URL' => 'CODE',
            'HEADER' => '',
            'Код' => 'PROPERTY_STARTVOLT_CODE',
            'Комплектация' => 'PROPERTY_OPTIONS',
            'Гарантия' => 'PROPERTY_WARRANTY',
            'Описание' => 'PREVIEW_TEXT',
            'Тип' => 'PROPERTY_TYPE',
        );
        $this->types_values = array(
            '1' => 'TYPE_NATIVE',
            '2' => 'TYPE_FOREIGN',
            '3' => 'TYPE_FREIGHT',
        );

        $this->PRODUCT_SPECIFICATIONS = 'PROPERTY_SPECIFICATIONS';
        $this->structure = array();
        $this->cars = array();
        $this->can_import = false;
        $this->start_time = time();
        $this->import_date = date('d.m.Y');
        $this->time = 10; //секунд
        $this->end_time = $this->start_time + $this->time;
        $this->import_actual_step = 0;
        $this->import_images_actual_step = 0;

        $this->count_of_import_products = 100; //импорт по 100 товаров
        $this->import_steps = array(
            '0' => array(
                'method' => 'ReadCsvProducts',
                'result' => array(),
                'return_property' => 'import_products_file_content',
                'description' => 'Загрузка файла с товарами...',
            ),
            '1' => array(
                'method' => 'ReadCsvSections',
                'result' => array(),
                'return_property' => 'import_sections_file_content',
                'description' => 'Загрузка файла с категориями...',
            ),
            '2' => array(
                'method' => 'ParseCsvProducts',
                'result' => array(),
                'return_property' => 'products_rows',
                'description' => 'Чтение файла с товарами...',
            ),
            '3' => array(
                'method' => 'ParseCsvSections',
                'result' => array(),
                'return_property' => 'sections_rows',
                'description' => 'Чтение файла с категориями...',
            ),
            '4' => array(
                'method' => 'CombineTables',
                'result' => array(),
                'return_property' => 'structure',
                'description' => 'Объединение таблиц...',
            ),
            '5' => array(
                'method' => 'MakeCars',
                'result' => array(),
                'return_property' => 'structure_db',
                'description' => 'Формирование списка автомобилей...',
            ),
            '6' => array(
                'method' => 'ImportSections',
                'result' => array(),
                'return_property' => 'structure_after_sections_import',
                'description' => 'Импорт категорий...',
            ),
            '7' => array(
                'method' => 'PrepareImportProducts',
                'result' => array(),
                'return_property' => 'structure_for_products_import',
                'description' => 'Подготовка к импорту товаров...',
            ),
            '8' => array(
                'method' => 'ImportProducts',
                'result' => array(),
                'return_property' => 'structure_after_products_import',
                'description' => 'Импорт товаров....',
            ),
        );
        $this->import_images_steps = array(
            '0' => array(
                'method' => 'SaveImageFile',
                'result' => array(),
                'return_property' => 'import_images_file',
                'description' => 'Загрузка файла с картинками...',
            ),
            '1' => array(
                'method' => 'ExtractImageArchive',
                'result' => array(),
                'return_property' => 'extracted_files',
                'description' => 'Распаковка файла с картинками...',
            ),
            '2' => array(
                'method' => 'ReadDir',
                'result' => array(),
                'return_property' => 'tmp_image_structure',
                'description' => 'Чтение картинок...',
            ),
            '3' => array(
                'method' => 'MakeProductStructure',
                'result' => array(),
                'return_property' => 'products_structure',
                'description' => 'Формирование структуры товаров...',
            ),
            '4' => array(
                'method' => 'FindImages',
                'result' => array(),
                'return_property' => 'products_structure_after_find_image',
                'description' => 'Поиск картинок по товарам...',
            ),
            '5' => array(
                'method' => 'UpdateImages',
                'result' => array(),
                'return_property' => '',
                'description' => 'Обновление картинок...',
            ),
            '6' => array(
                'method' => 'FinishImportImages',
                'result' => array(),
                'return_property' => '',
                'description' => 'Завершение импорта...',
            ),
        );

        $this->import_shops_steps = array(
            '0' => array(
                'method' => 'SaveShopsFile',
                'result' => array(),
                'return_property' => 'import_shops_file',
                'description' => 'Загрузка файла с точками продаж...',
            ),
            '1' => array(
                'method' => 'ReadCsvShops',
                'result' => array(),
                'return_property' => 'import_shops_file_content',
                'description' => 'Чтение файла...',
            ),
            '2' => array(
                'method' => 'ParseCsvCountries',
                'result' => array(),
                'return_property' => 'countries_rows',
                'description' => 'Загрузка стран...',
            ),
            '3' => array(
                'method' => 'ParseCsvRegions',
                'result' => array(),
                'return_property' => 'regions_rows',
                'description' => 'Загрузка областей...',
            ),
            '4' => array(
                'method' => 'ParseCsvCities',
                'result' => array(),
                'return_property' => 'cities_rows',
                'description' => 'Загрузка городов...',
            ),
            '5' => array(
                'method' => 'ParseCsvTypesShops',
                'result' => array(),
                'return_property' => 'types_rows',
                'description' => 'Загрузка типов точек продаж...',
            ),
            '6' => array(
                'method' => 'ParseCsvShops',
                'result' => array(),
                'return_property' => 'shops_rows',
                'description' => 'Загрузка точек продаж...',
            ),
            '7' => array(
                'method' => 'UpdateShops',
                'result' => array(),
                'return_property' => '',
                'description' => 'Загрузка в базу...',
            ),
        );
        CModule::IncludeModule('iblock');
        $this->bs = new CIBlockSection;
        $this->el = new CIBlockElement;
    }

    public function CanImport() {
        if (!empty($this->import_products_file) && !empty($this->import_sections_file)) {
            $this->can_import = true;
        } else {
            $this->can_import = false;
        }
    }

    public function CheckImportFile() {
        $res_pr = CFile::GetList(array("TIMESTAMP_X" => "desc"), array("MODULE_ID" => $this->PRODUCTS_MODULE_ID));
        $p = 0;
        while ($res_arr = $res_pr->GetNext()) {
            if ($p == 0) {
                if (file_exists($this->DOCUMENT_ROOT . CFile::GetPath($res_arr['ID']))) {
                    $this->import_products_file = array(
                        'ID' => $res_arr['ID'],
                        'NAME' => $res_arr['ORIGINAL_NAME'],
                        'PATH' => CFile::GetPath($res_arr['ID']),
                        'DATE' => $res_arr['TIMESTAMP_X'],
                    );
                } else {
                    CFile::Delete($res_arr['ID']);
                }
            } else {
                CFile::Delete($res_arr['ID']);
            }
            $p++;
        }
        $res_sec = CFile::GetList(array("TIMESTAMP_X" => "desc"), array("MODULE_ID" => $this->SECTIONS_MODULE_ID));
        $s = 0;
        while ($res_arr = $res_sec->GetNext()) {
            if ($s == 0) {
                if (file_exists($this->DOCUMENT_ROOT . CFile::GetPath($res_arr['ID']))) {
                    $this->import_sections_file = array(
                        'ID' => $res_arr['ID'],
                        'NAME' => $res_arr['ORIGINAL_NAME'],
                        'PATH' => CFile::GetPath($res_arr['ID']),
                        'DATE' => $res_arr['TIMESTAMP_X'],
                    );
                } else {
                    CFile::Delete($res_arr['ID']);
                }
            } else {
                CFile::Delete($res_arr['ID']);
            }
            $s++;
        }
        $this->CanImport();
    }

    public function MoveFileToLog($MODULE_ID) {
        $this->CheckImportFile();
        $log_module = $MODULE_ID . '_log';
        $file = $MODULE_ID . '_file';
        $file = $this->$file;
        $file_array = CFile::MakeFileArray($file['ID']);
        $info = Array("MODULE_ID" => $log_module);
        $arFILE = array_merge($file_array, $info);
        $fid = CFile::SaveFile($arFILE, $log_module);
        CFile::Delete($file['ID']);
    }

    public function CheckTime() {
        $time = time();
        if ($time < $this->end_time) {
            return false;
        }
        $_SESSION['IMPORT_TIME'] = $time - $_SESSION['IMPORT_START'];
        return true;
    }

    public function GetTime() {
        $time = time();
        $_SESSION['IMPORT_TIME'] = $time - $_SESSION['IMPORT_START'];
        if ($_SESSION['IMPORT_TIME'] < 60) {
            return $_SESSION['IMPORT_TIME'] . ' ' . DeclOfNum($_SESSION['IMPORT_TIME'], array('секунду', 'секунды', 'секунд'));
        } elseif ($_SESSION['IMPORT_TIME'] < 3600) {
            $minutes = $_SESSION['IMPORT_TIME'] / 60;
            $minutes = floor($minutes);
            $sec = $_SESSION['IMPORT_TIME'] % 60;
            return $minutes . ' ' . DeclOfNum($minutes, array('минуту', 'минуты', 'минут')) . ', ' . $sec . ' ' . DeclOfNum($sec, array('секунду', 'секунды', 'секунд'));
        }
    }

    function Import() {
        //тут работа с кешем, результаты работы скрипта храним в кеше
        if (empty($_SESSION['IMPORT_STEP'])) {
            $_SESSION['IMPORT_STEP'] = 0;
        }

        $this->CheckImportFile();
        $this->import_actual_step = $_SESSION['IMPORT_STEP'];
        $obCache = new CPHPCache;

        for ($i = 0; $i < $this->import_actual_step; $i++) {
            $cache_time = 360000;
            $cache_id = 'import-' . $this->import_date . '-' . session_id() . '-' . $i;
            $cache_dir = "/import/" . $this->import_date . '/' . session_id() . '/' . $i . '/';
            if ($obCache->InitCache($cache_time, $cache_id, $cache_dir)) {
                $res = $obCache->GetVars();
                $arResult = $res['arResult'];
                if (!empty($arResult['result'])) {
                    $return_property = $arResult['return_property'];
                    $this->$return_property = $arResult['result'];
                }
            } else {
                
            }
        }
        //если это импорт товаров, то загружаем предыдущий результат
        if ($this->import_actual_step == 8) {
            $cache_time = 360000;
            $cache_id = 'import-' . $this->import_date . '-' . session_id() . '-' . $this->import_actual_step;
            $cache_dir = "/import/" . $this->import_date . '/' . session_id() . '/' . $this->import_actual_step . '/';
            if ($obCache->InitCache($cache_time, $cache_id, $cache_dir)) {
                $res = $obCache->GetVars();
                $arResult = $res['arResult'];
                if (!empty($arResult['result'])) {
                    $return_property = $arResult['return_property'];
                    $this->$return_property = $arResult['result'];
                    //убили кеш
                    DeleteDirFilesEx("/bitrix/cache" . $cache_dir);
                }
            }
        }
        if (!empty($this->structure_after_products_import)) {
            if ($this->structure_after_products_import['success'] == 1) {
                $_SESSION['IMPORT_STEP'] = $this->import_actual_step + 1;
                //убиваем весь кеш
                DeleteDirFilesEx("/bitrix/cache/import/");
                DeleteDirFilesEx("/bitrix/cache/products.filter/");
                DeleteDirFilesEx("/bitrix/cache/products.autos/");
                return $_SESSION['IMPORT_STEP'];
            }
        }


        //смотрим на каком мы шаге
        $step = $this->import_steps[$this->import_actual_step];
        $method = $step['method'];
        if (method_exists($this, $method)) {
            $this->import_steps[$this->import_actual_step]['result'] = $this->$method();
        } else {
            $_SESSION['IMPORT_STEP'] = count($this->import_steps) + 1;
            return $_SESSION['IMPORT_STEP'];
        }
        //кешируем
        $cache_time = 360000;
        $cache_id = 'import-' . $this->import_date . '-' . session_id() . '-' . $this->import_actual_step;
        $cache_dir = "/import/" . $this->import_date . '/' . session_id() . '/' . $this->import_actual_step . '/';
        $obCache->StartDataCache($cache_time, $cache_id, $cache_dir);
        $obCache->EndDataCache(array("arResult" => $this->import_steps[$this->import_actual_step]));

        //записываем следующий щаг
        if ($this->import_actual_step != 8) {
            $_SESSION['IMPORT_STEP'] = $this->import_actual_step + 1;
        }
        return $_SESSION['IMPORT_STEP'];
    }

    public function CheckFlag($flag, $string) {
        $pos_without_DD = strpos($string, $flag);
        $pos_with_DD = strpos($string, '""' . $flag . '""');
        if ($pos_without_DD === false && $pos_with_DD === false) {
            return false;
        } else {
            return true;
        }
    }

    public function CheckNextLine($value) {
        $pos = strpos($value, $this->line);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    public function ReplaseLine($value) {
        return str_replace($this->line, '', $value);
    }

    public function ReplaseFlag($flag, $value) {
        return str_replace($flag, '', $value);
    }

    public function GetParentCategory($flag) {
        $subcategory = str_replace($this->catalog_flag, '', $flag);
        $subcategory = str_replace('"', '', $subcategory);
        $subcategory = trim($subcategory);
        if (empty($subcategory)) {
            return 0;
        }
        $depend = explode($this->sub_category_separator, $subcategory);
        if ($depend[0] == $depend[1]) {
            return 0;
        }
        return $depend[1];
    }

    public function GetCategoryNumber($flag) {
        $subcategory = str_replace($this->catalog_flag, '', $flag);
        $subcategory = str_replace('"', '', $subcategory);
        $subcategory = trim($subcategory);
        if (empty($subcategory)) {
            return 0;
        }
        $depend = explode($this->sub_category_separator, $subcategory);
        return $depend[0];
    }

    //Вспомогательный метод, транслитит имя
    function translitName($name) {
        $params = array("replace_space" => "-", "replace_other" => "-");
        $result = Cutil::translit($name, "ru", $params);
        return $result;
    }

    public function getSelect($CODE, $IBLOCK_ID) {
        $types = array();
        $property_enums = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC"), Array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => $CODE));
        while ($enum_fields = $property_enums->GetNext()) {
            $types[] = array(
                'ID' => $enum_fields['ID'],
                'VALUE' => $enum_fields['VALUE'],
                'XML_ID' => $enum_fields['XML_ID'],
            );
        }
        return $types;
    }

    public function CheckCarMark($mark_code) {
        $CODE = $this->translitName($mark_code);
        $arFilter = Array('IBLOCK_ID' => $this->CARS_IBLOCK_ID, 'GLOBAL_ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1, 'CODE' => $CODE);
        $db_list = CIBlockSection::GetList(Array('SORT' => 'ASC', 'CREATED' => 'ASC'), $arFilter, true);
        if ($mark = $db_list->GetNext()) {
            return $mark['ID'];
        } else {
            $arFields = Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $this->CARS_IBLOCK_ID,
                "NAME" => $mark_code,
                "CODE" => $CODE,
            );
            $mark_id = $this->bs->Add($arFields);
            return $mark_id;
        }
    }

    public function CheckCarModel($mark_id, $model) {
        $CODE = $this->translitName($model['NAME']);
        $arSelect = Array();
        $arFilter = Array("IBLOCK_ID" => $this->CARS_IBLOCK_ID, "ACTIVE" => "Y", 'CODE' => $CODE, 'SECTION_ID' => $mark_id);
        $res = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        $model_id = false;
        while ($model_db = $res->GetNextElement()) {
            $arFields = $model_db->GetFields();
            $arProps = $model_db->GetProperties();
            $changes = false;
            foreach ($model as $property_code => $property_value) {
                $pos_PROPERTY = strpos($property_code, 'PROPERTY_');
                $value = false;
                if ($pos_PROPERTY !== false) {
                    $PROPERTY_CODE = str_replace('PROPERTY_', '', $property_code);
                    if ($PROPERTY_CODE == 'AC' ||
                            $PROPERTY_CODE == 'ABS' ||
                            $PROPERTY_CODE == 'UR' ||
                            $PROPERTY_CODE == 'KPP') {
                        $property_value = trim($property_value);
                        $property_list_values = $this->getSelect($PROPERTY_CODE, $this->CARS_IBLOCK_ID);
                        foreach ($property_list_values as $property_list_values_key => $property_list_value) {
                            if ($property_list_value['VALUE'] == $property_value) {
                                $value = $property_list_value['VALUE'];
                            }
                        }
                    } else {
                        $value = $property_value;
                    }
                    if (!$value) {
                        $value = '';
                    }
                    if ($arProps[$PROPERTY_CODE]['MULTIPLE'] == 'N') {
                        if ($value != $arProps[$PROPERTY_CODE]['VALUE']) {
                            $changes = true;
                        }
                    } else {
                        foreach ($arProps[$PROPERTY_CODE]['VALUE'] as $value_key => $multy_value) {
                            if (!in_array($multy_value, $value)) {
                                $changes = true;
                            }
                        }
                    }
                }
            }
            if (!$changes) {
                return $arFields['ID'];
            }
        }
        if (!$model_id) {
            //формируем модель и добавляем ее в бд
            $PROP = array();
            foreach ($model as $property_code => $property_value) {
                $pos_PROPERTY = strpos($property_code, 'PROPERTY_');
                if ($pos_PROPERTY !== false) {
                    $PROPERTY_CODE = str_replace('PROPERTY_', '', $property_code);
                    if ($PROPERTY_CODE == 'AC' ||
                            $PROPERTY_CODE == 'ABS' ||
                            $PROPERTY_CODE == 'UR' ||
                            $PROPERTY_CODE == 'KPP') {
                        $property_value = trim($property_value);
                        $property_list_values = $this->getSelect($PROPERTY_CODE, $this->CARS_IBLOCK_ID);
                        foreach ($property_list_values as $property_list_values_key => $property_list_value) {
                            if ($property_list_value['VALUE'] == $property_value) {
                                $PROP[$PROPERTY_CODE] = $property_list_value['ID'];
                            }
                        }
                    } else {
                        $PROP[$PROPERTY_CODE] = $property_value;
                    }
                }
            }
            $arLoadModelArray = Array(
                "IBLOCK_SECTION_ID" => $mark_id,
                "IBLOCK_ID" => $this->CARS_IBLOCK_ID,
                "CODE" => $CODE,
                "PROPERTY_VALUES" => $PROP,
                "NAME" => $model['NAME'],
                "ACTIVE" => "Y", // активен
            );
            $model_id = $this->el->Add($arLoadModelArray);
        }
        return $model_id;
    }

    public function CheckCategory($parent_id, $category, $sort) {
        $CODE = $this->translitName($category['NAME']);
        $arFilter = Array('IBLOCK_ID' => $this->PRODUCTS_IBLOCK_ID, 'CODE' => $CODE);
        $db_list = CIBlockSection::GetList(Array('SORT' => 'ASC', 'CREATED' => 'ASC'), $arFilter, true);
        if ($category_db = $db_list->GetNext()) {
            $arFields = Array(
                "SORT" => $sort,
                "IBLOCK_SECTION_ID" => $parent_id,
                "ACTIVE" => "Y",
                "DESCRIPTION" => $category['DESCRIPTION'],
                "DESCRIPTION_TYPE" => 'html',
            );
            $res = $this->bs->Update($category_db['ID'], $arFields);
            return $category_db['ID'];
        } else {
            $arFields = Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID,
                "IBLOCK_SECTION_ID" => $parent_id,
                "NAME" => $category['NAME'],
                "CODE" => $CODE,
                "SORT" => $sort,
                "DESCRIPTION" => $category['DESCRIPTION'],
                "DESCRIPTION_TYPE" => 'html',
            );
            $category_id = $this->bs->Add($arFields);
            return $category_id;
        }
    }

    public function ImportOneProduct($category_id, $product, $sort) {
        $CODE = $this->translitName($product['CODE']) . '-' . $this->translitName($product['PROPERTY_VALUES']['STARTVOLT_CODE']);
        $arSelect = Array();
        $arFilter = Array("IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID, 'CODE' => $CODE);
        $res = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        if ($product_db = $res->GetNext()) {
            //SPECIFICATIONS
            CIBlockElement::SetPropertyValueCode($product_db['ID'], 'SPECIFICATIONS', $product['PROPERTY_VALUES']['SPECIFICATIONS']);

            CIBlockElement::SetPropertyValuesEx($product_db['ID'], $this->PRODUCTS_IBLOCK_ID, $product['PROPERTY_VALUES']);
            $arLoadProductArray = Array(
                "IBLOCK_SECTION_ID" => $category_id,
                "PREVIEW_TEXT" => $product['PREVIEW_TEXT'],
                "PREVIEW_TEXT_TYPE" => "html",
                "ACTIVE" => "Y", // активен
                "SORT" => $sort
            );
            $res = $this->el->Update($product_db['ID'], $arLoadProductArray);
        } else {
            $PROP = $product['PROPERTY_VALUES'];
            $arLoadProductArray = Array(
                "IBLOCK_SECTION_ID" => $category_id,
                "IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID,
                "CODE" => $CODE,
                "PROPERTY_VALUES" => $PROP,
                "NAME" => $product["NAME"],
                "ACTIVE" => "Y", // активен
                "PREVIEW_TEXT" => $product['PREVIEW_TEXT'],
                "PREVIEW_TEXT_TYPE" => "html",
                "SORT" => $sort
            );
            $product_id = $this->el->Add($arLoadProductArray);
        }
    }

    //методы шагов импорта
    public function ReadCsvProducts() {
        global $APPLICATION;
        $path = $this->DOCUMENT_ROOT . $this->import_products_file['PATH'];
        $this->import_products_file_content = $APPLICATION->GetFileContent($path);
        //тут же деактивируем все продукты, когда будем импортировать сделаем активными только те, что есть в каталоге.
        $arSelect = Array();
        $arFilter = Array("IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID);
        $res_products = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        while ($product_db = $res_products->GetNext()) {
            $arLoadProductArray = Array(
                "ACTIVE" => "N", // не активен
            );
            $res = $this->el->Update($product_db['ID'], $arLoadProductArray);
        }
        return $this->import_products_file_content;
    }

    public function ReadCsvSections() {
        global $APPLICATION;
        $path = $this->DOCUMENT_ROOT . $this->import_sections_file['PATH'];
        $import_sections_file_content = $APPLICATION->GetFileContent($path);

        $this->import_sections_file_content = str_replace('&nbsp;', '', $import_sections_file_content);

        //тут же деактивируем все категории, когда будем импортировать сделаем активными только те, что есть в каталоге.
        $arFilter = Array('IBLOCK_ID' => $this->PRODUCTS_IBLOCK_ID);
        $db_list = CIBlockSection::GetList(Array('SORT' => 'ASC', 'CREATED' => 'ASC'), $arFilter, true);
        while ($category_db = $db_list->GetNext()) {
            $arFields = Array(
                "ACTIVE" => "N", //не активна
            );
            $res = $this->bs->Update($category_db['ID'], $arFields);
        }

        return $this->import_sections_file_content;
    }

    public function ParseCsvProducts() {
        $this->products_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_products_file_content);
        $k = 0;
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            $value_array = explode($this->field_separator, $value);
            foreach ($value_array as $key => $value_value) {
                //первая строка - это заголовки
                if ($k == 0) {
                    $structure_scheme[$key] = $value_value;
                } else {
                    $structure[$k - 1][$structure_scheme[$key]] = $value_value;
                }
            }
            $k++;
        }
        $_structure = array();
        foreach ($structure as $key => $product) {
            $_structure[$product[$this->combine_code_products]][] = $product;
        }
        $this->products_rows = $_structure;

        return $this->products_rows;
    }

    public function ParseCsvSections() {
        $this->sections_rows = array();
        $array = explode($this->field_separator, $this->import_sections_file_content);
        /*
         * Тема такая: бежим по всем элементам массива. Флаг описания категории (и собственно самой категории): $this->catalog_flag
         * Флаг начала продуктов: $this->header_flag. 
         * Флаг продукта: $this->product_flag. 
         * Также, подкатегории выглядется следующим образом:
         * $this->catalog_flag.'номер категории (с 1)::номер_подкатегории'
         * Если номер подгатегории=номер категории значит это родительская категория
         */
        //print_r($array);
        $structure = array();
        $category_number = 0;
        $options = array();
        $header_mode = false;
        $pr = 1;
        $product_mode = false;
        $product = array('HEADER' => '');
        $first_category = true;
        for ($i = 0; $i < count($array); $i++) {
            $value = $array[$i];
            $value = str_replace('"', '', $value);
            if ($this->CheckFlag($this->catalog_flag, $value)) {
                if (!$first_category) {
                    $structure[$category_number]['PRODUCTS'][$product[$this->combine_code_category]] = $product;
                    $product = array('HEADER' => '');
                    $pr = 1;
                }
                if ($first_category) {
                    $first_category = false;
                }
                $product_mode = false;
                $header_mode = false;
                $category_number++;
                $structure[$category_number] = array(
                    'NAME' => str_replace('"', '', $array[$i + 1]),
                    'NUMBER' => $this->GetCategoryNumber($value),
                    'DESCRIPTION' => $array[$i + 2],
                    'PARENT' => $this->GetParentCategory($value),
                    'PRODUCTS' => array(),
                );
            }
            if ($product_mode) { //значит бежим по продуктам
                if (!$this->CheckFlag($this->product_flag, $value)) {
                    if (!empty($options[$pr])) {
                        $product[$options[$pr]] = $value;
                    }

                    $pr++;
                } else {
                    $value = $this->ReplaseFlag($this->product_flag, $value);
                    if (!empty($options[$pr])) {
                        $product[$options[$pr]] = $value;
                    }

                    $structure[$category_number]['PRODUCTS'][$product[$this->combine_code_category]] = $product;
                    $product = array('HEADER' => '');
                    $pr = 1;
                }
            }
            if ($header_mode) { //значит бежим по заголовку, заголовок будет до тех пор, пока не встретится $this->product_flag
                if (!$this->CheckFlag($this->product_flag, $value)) {
                    $options[] = $value;
                } else {
                    $value = $this->ReplaseFlag($this->product_flag, $value);
                    $header_mode = false;
                    $product_mode = true;
                    $product = array('HEADER' => '');
                    $pr = 1;
                }
            }


            if ($this->CheckFlag($this->header_flag, $value)) {

                $options = array('HEADER');
                $header_mode = true;
                $product_mode = false;
            }
        }
        $structure[$category_number]['PRODUCTS'][$product[$this->combine_code_category]] = $product;
        $this->sections_rows = $structure;
        return $this->sections_rows;
    }

    public function CombineTables() {
        $this->structure = $this->sections_rows;
        foreach ($this->sections_rows as $category_number => $category) {
            foreach ($category['PRODUCTS'] as $_product_key => $product) {

                $products_rows = $this->products_rows[$product[$this->combine_code_category]];
                $db_product = array();
                foreach ($this->db_fields_products as $key => $CODE) {
                    if (!empty($CODE)) {
                        foreach ($products_rows as $products_rows_key => $products_row) {
                            if (!in_array($products_row[$key], $db_product[$CODE])) {
                                $db_product[$CODE][] = $products_row[$key];
                            }
                        }
                        if (count($db_product[$CODE]) == 1) {
                            $val = $db_product[$CODE][0];
                            $db_product[$CODE] = $val;
                        }
                    }
                }
                foreach ($product as $product_key => $product_key_value) {
                    if (isset($this->db_fields_sections[$product_key])) {
                        $CODE = $this->db_fields_sections[$product_key];
                        if (!empty($CODE)) {
                            $db_product[$CODE] = $product_key_value;
                        }
                    } else {
                        if (!empty($product_key_value)) {
                            $db_product[$this->PRODUCT_SPECIFICATIONS][] = array(
                                'DESCRIPTION' => $product_key,
                                'VALUE' => $product_key_value,
                            );
                        }
                    }
                }
                $_db_product = array();
                foreach ($db_product as $key => $value) {
                    $pos_PROPERTY = strpos($key, 'PROPERTY_');
                    if ($pos_PROPERTY === false) {
                        $_db_product[$key] = $value;
                    } else {
                        $PROPERTY_CODE = str_replace('PROPERTY_', '', $key);
                        $_db_product['PROPERTY_VALUES'][$PROPERTY_CODE] = $value;
                    }
                }

                $_db_product['CARS'] = array();
                $CARS = array();
                $car_key = 0;
                foreach ($products_rows as $__key => $products_rows_value) {
                    foreach ($products_rows_value as $value_code => $value) {
                        if (!empty($this->db_fields_cars[$value_code])) {
                            if ($this->ignore_car != $value && !empty($value)) {
                                switch ($this->db_fields_cars[$value_code]) {
                                    case 'CATEGORY_NAME':
                                        $CARS[$car_key][$value] = array(
                                            'NAME' => $value,
                                            'MODELS' => array()
                                        );
                                        $mark = $value;
                                        break;
                                    case 'ELEMENT_NAME':
                                        $models = explode(',', $value);
                                        foreach ($models as $model_key => $model) {
                                            $model = trim($model);
                                            $CARS[$car_key][$mark]['MODELS'][$model] = array(
                                                'NAME' => $model,
                                                'PROPERTY_ENGINE' => array(),
                                                'PROPERTY_AC' => '',
                                                'PROPERTY_ABS' => '',
                                                'PROPERTY_UR' => '',
                                                'PROPERTY_KPP' => '',
                                                'PROPERTY_YEAR' => array(),
                                            );
                                        }
                                        break;
                                    case 'PROPERTY_ENGINE':
                                        $engines = explode('/', $value);
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            foreach ($engines as $engine_key => $engine) {
                                                $engine = trim($engine);
                                                $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_ENGINE'][] = $engine;
                                            }
                                        }
                                        break;
                                    case 'PROPERTY_AC':
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_AC'] = trim($value);
                                        }
                                        break;
                                    case 'PROPERTY_ABS':
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_ABS'] = trim($value);
                                        }
                                        break;
                                    case 'PROPERTY_UR':
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_UR'] = trim($value);
                                        }
                                        break;
                                    case 'PROPERTY_KPP':
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_KPP'] = trim($value);
                                        }
                                        break;
                                    case 'PROPERTY_YEAR':
                                        $years_start_end = explode('-', $value);
                                        $start = (int) trim($years_start_end[0]);
                                        $end = (!empty($years_start_end[1]) ? (int) trim($years_start_end[1]) : (int) date('Y'));
                                        foreach ($CARS[$car_key][$mark]['MODELS'] as $model_name => $model) {
                                            for ($i = $start; $i <= $end; $i++) {
                                                $CARS[$car_key][$mark]['MODELS'][$model_name]['PROPERTY_YEAR'][] = $i;
                                            }
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                    $car_key++;
                }
                $_db_product['CARS'] = $CARS;
                $this->structure[$category_number]['PRODUCTS'][$_product_key] = $_db_product;
            }
        }
        return $this->structure;
    }

    public function MakeCars() {
        $this->structure_db = $this->structure;
        foreach ($this->structure_db as $category_number => $category) {
            foreach ($category['PRODUCTS'] as $product_code => $product) {
                foreach ($product['CARS'] as $key => $car) {
                    foreach ($car as $mark_name => $mark) {
                        $mark_id = $this->CheckCarMark($mark_name);
                        foreach ($mark['MODELS'] as $model_name => $model) {
                            $model_id = $this->CheckCarModel($mark_id, $model);
                            if (!in_array($model_id, $this->structure_db[$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'])) {
                                $this->structure_db[$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'][] = $model_id;
                            }
                        }
                    }
                }
                if (empty($this->structure_db[$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'])) {
                    $this->structure_db[$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'] = false;
                }
                unset($this->structure_db[$category_number]['PRODUCTS'][$product_code]['CARS']);
            }
        }

        return $this->structure_db;
    }

    public function ImportSections() {
        $this->structure_after_sections_import = $this->structure_db;
        foreach ($this->structure_after_sections_import as $category_number => $category) {
            $parent_id = false;
            if (!empty($category['PARENT'])) {
                foreach ($this->structure_after_sections_import as $_check_category_id => $check_category) {
                    if ($check_category['NUMBER'] == $category['PARENT']) {
                        $parent_id = $this->structure_after_sections_import[$_check_category_id]['ID'];
                    }
                }
            }
            $category_id = $this->CheckCategory($parent_id, $category, $category_number);
            $this->structure_after_sections_import[$category_number]['ID'] = $category_id;
        }
        return $this->structure_after_sections_import;
    }

    public function PrepareImportProducts() {
        $this->structure_for_products_import = array();
        foreach ($this->structure_after_sections_import as $category_number => $category) {
            $this->structure_for_products_import[$category_number] = array(
                'NAME' => $category['NAME'],
                'ID' => $category['ID'],
                'PRODUCTS' => array(),
            );
            foreach ($category['PRODUCTS'] as $product_code => $product) {
                if (!empty($product['PROPERTY_VALUES']['NEW_PROD'])) {
                    $NEW_values = $this->getSelect('NEW_PROD', $this->PRODUCTS_IBLOCK_ID);
                    $product['PROPERTY_VALUES']['NEW_PROD'] = $NEW_values[0]['ID'];
                } else {
                    $product['PROPERTY_VALUES']['NEW_PROD'] = '';
                }
                if (!empty($product['PROPERTY_VALUES']['POPULAR_PROD'])) {
                    $POPULAR_values = $this->getSelect('POPULAR_PROD', $this->PRODUCTS_IBLOCK_ID);
                    $product['PROPERTY_VALUES']['POPULAR_PROD'] = $POPULAR_values[0]['ID'];
                } else {
                    $product['PROPERTY_VALUES']['POPULAR_PROD'] = '';
                }
                foreach ($this->types_values as $types_values_key => $types_values_value) {
                    $product['PROPERTY_VALUES'][$types_values_value] = '';
                }

                if (!empty($product['PROPERTY_VALUES']['TYPE'])) {
                    $TYPE_NATIVE_values = $this->getSelect('TYPE_NATIVE', $this->PRODUCTS_IBLOCK_ID);
                    $TYPE_FOREIGN_values = $this->getSelect('TYPE_FOREIGN', $this->PRODUCTS_IBLOCK_ID);
                    $TYPE_FREIGHT_values = $this->getSelect('TYPE_FREIGHT', $this->PRODUCTS_IBLOCK_ID);
                    $types = explode(',', $product['PROPERTY_VALUES']['TYPE']);
                    unset($product['PROPERTY_VALUES']['TYPE']);
                    foreach ($types as $types_key => $type) {
                        foreach ($this->types_values as $types_values_key => $types_values_value) {
                            if ($type == $types_values_key) {
                                $var = $types_values_value . '_values';
                                $TYPE_VAR = $$var;
                                $product['PROPERTY_VALUES'][$types_values_value] = $TYPE_VAR[0]['ID'];
                            }
                        }
                    }
                } else {
                    unset($product['PROPERTY_VALUES']['TYPE']);
                }


                $OPTION_TEXT = $product['PROPERTY_VALUES']['OPTIONS'];
                $product['PROPERTY_VALUES']['OPTIONS'] = Array("VALUE" => Array("TEXT" => $OPTION_TEXT, "TYPE" => "html"));
                $WARRANTY_TEXT = $product['PROPERTY_VALUES']['WARRANTY'];
                $product['PROPERTY_VALUES']['WARRANTY'] = Array("VALUE" => Array("TEXT" => $WARRANTY_TEXT, "TYPE" => "html"));

                $product['PREVIEW_TEXT_TYPE'] = 'html';

                $this->structure_for_products_import[$category_number]['PRODUCTS'][] = $product;
            }
        }
        return $this->structure_for_products_import;
    }

    public function ImportProducts() {
        if (empty($this->structure_after_products_import)) {
            $this->structure_after_products_import['structure'] = $this->structure_for_products_import;
            $this->structure_after_products_import['category_step'] = 1;
            $this->structure_after_products_import['product_step'] = 0;
            $this->structure_after_products_import['success'] = 0;
        }

        //импорт по $this->count_of_import_products товаров
        $import_product = 0;
        foreach ($this->structure_after_products_import['structure'] as $category_number => $category) {
            if ($category_number == $this->structure_after_products_import['category_step']) {
                foreach ($category['PRODUCTS'] as $key_product => $product) {
                    if ($key_product == $this->structure_after_products_import['product_step']) {
                        $this->ImportOneProduct($category['ID'], $product, $key_product);
                        $import_product++;
                        if ($import_product == $this->count_of_import_products) {
                            $this->structure_after_products_import['category_step'] = $category_number;
                            $this->structure_after_products_import['product_step'] = $key_product + 1;
                            return $this->structure_after_products_import;
                        }
                        $this->structure_after_products_import['product_step'] = $key_product + 1;
                    }
                }
                $this->structure_after_products_import['category_step'] = $category_number + 1;
                $this->structure_after_products_import['product_step'] = 0;
            }
        }
        if (empty($this->structure_after_products_import['structure'][$this->structure_after_products_import['category_step']])) {
            $this->structure_after_products_import['success'] = 1;
        }

        return $this->structure_after_products_import;
    }

    //Импорт картинок
    public function ImportImages() {
        $this->import_error = '';
        //тут работа с кешем, результаты работы скрипта храним в кеше
        if (empty($_SESSION['IMPORT_STEP'])) {
            $_SESSION['IMPORT_STEP'] = 0;
        }
        $this->import_images_actual_step = $_SESSION['IMPORT_STEP'];
        $obCache = new CPHPCache;
        for ($i = 0; $i < $this->import_images_actual_step; $i++) {
            $cache_time = 360000;
            $cache_id = 'import_images-' . $this->import_date . '-' . session_id() . '-' . $i;
            $cache_dir = "/import_images/" . $this->import_date . '/' . session_id() . '/' . $i . '/';
            if ($obCache->InitCache($cache_time, $cache_id, $cache_dir)) {
                $res = $obCache->GetVars();
                $arResult = $res['arResult'];
                if (!empty($arResult['result'])) {
                    $return_property = $arResult['return_property'];
                    $this->$return_property = $arResult['result'];
                }
            } else {
                
            }
        }
        //смотрим на каком мы шаге
        $step = $this->import_images_steps[$this->import_images_actual_step];
        $method = $step['method'];
        if (method_exists($this, $method)) {
            $this->import_images_steps[$this->import_images_actual_step]['result'] = $this->$method();
            if (!$this->import_images_steps[$this->import_images_actual_step]['result']) {
                $_SESSION['IMPORT_STEP'] = count($this->import_images_steps) + 1;
                return false;
            }
        } else {
            $_SESSION['IMPORT_STEP'] = count($this->import_images_steps) + 1;
            return $_SESSION['IMPORT_STEP'];
        }
        //кешируем

        $cache_time = 360000;
        $cache_id = 'import_images-' . $this->import_date . '-' . session_id() . '-' . $this->import_images_actual_step;
        $cache_dir = "/import_images/" . $this->import_date . '/' . session_id() . '/' . $this->import_images_actual_step . '/';
        $obCache->StartDataCache($cache_time, $cache_id, $cache_dir);
        $obCache->EndDataCache(array("arResult" => $this->import_images_steps[$this->import_images_actual_step]));

        $_SESSION['IMPORT_STEP'] = $this->import_images_actual_step + 1;



        return $_SESSION['IMPORT_STEP'];
    }

    //вспомогательные методы
    public function CheckImportImageFile() {
        $res_pr = CFile::GetList(array("TIMESTAMP_X" => "desc"), array("MODULE_ID" => $this->IMAGES_MODULE_ID));
        while ($res_arr = $res_pr->GetNext()) {
            CFile::Delete($res_arr['ID']);
        }
    }

    //методы импорта

    public function SaveImageFile() {
        $file = $this->image_archive_file;
        //сохраним файл во временную папку
        $tmp_path = $this->upload_path_images . 'tmp';
        $this->CheckImportImageFile();
        $info = Array("MODULE_ID" => $this->IMAGES_MODULE_ID);
        $arFILE = array_merge($file, $info);

        $fid = CFile::SaveFile($arFILE, $tmp_path);

        $import_images_file = CFile::GetByID($fid)->GetNext();
        $this->import_images_file = array(
            'ID' => $import_images_file['ID'],
            'NAME' => $import_images_file['ORIGINAL_NAME'],
            'PATH' => CFile::GetPath($import_images_file['ID']),
            'DATE' => $import_images_file['TIMESTAMP_X'],
        );
        return $this->import_images_file;
    }

    public function ExtractImageArchive() {
        require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/zip.php");
        $oArchiver = new CZip($_SERVER["DOCUMENT_ROOT"] . $this->import_images_file['PATH']);
        $tres = $oArchiver->Unpack($_SERVER["DOCUMENT_ROOT"] . $this->upload_path . $this->upload_path_images . $this->upload_tmp_path_images);
        $arErrors = &$oArchiver->GetErrors();
        if (!$tres) {
            $this->import_error = 'Ошибка распаковки архива. Попробуйте обновить кеш';
            $this->extracted_files = false;
            return $this->extracted_files;
        }
        $this->extracted_files = true;
        return $this->extracted_files;
    }

    public function ReadDir() {
        $reading = $this->read_rec($_SERVER["DOCUMENT_ROOT"] . $this->upload_path . $this->upload_path_images . $this->upload_tmp_path_images);
        if (!$reading) {
            $this->import_error = 'Ошибка чтения директории. Попробуйте обновить кеш';
            return false;
        }
        return $this->tmp_image_structure;
    }

    public function read_rec($dr) {
        $dir .= $dr;
        $indent = sizeof(explode("/", $dir));
        $hndl = opendir($dir);
        if (!$hndl) {
            return false;
        }
        while (false !== ($str = readdir($hndl))) {
            if (($str != ".") && ($str != "..")) {
                $str = $dir . "/" . $str;
                if (is_dir($str)) {
                    $reading = $this->read_rec($str);
                    if (!$reading) {
                        return false;
                    }
                } else {
                    $arFile = CFile::MakeFileArray($str);
                    $res = CFile::CheckImageFile($arFile);
                    if (!$res) {
//                        $rif = CFile::ResizeImageFile(// уменьшение картинки
//                                        $str, $a = $_SERVER["DOCUMENT_ROOT"] . $this->upload_path . $this->upload_path_images . "small/" . $arFile['name'], array('width' => '400', 'height' => '300'), BX_RESIZE_IMAGE_PROPORTIONAL, array(), false, false);
//                        if ($rif) {
//                            unlink($str);
//                            rename($a, $str);
//                            unlink($a);
//                        }
//                        $arFile = CFile::MakeFileArray($str);
                        $this->tmp_image_structure[] = $arFile;
                    }
                }
            }
        }
        closedir($hndl);
        return true;
    }

    public function MakeProductStructure() {
        $arSelect = Array('IBLOCK_ID', 'ID', 'PROPERTY_STARTVOLT_CODE');
        $arFilter = Array("IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID);
        $res = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        while ($product_db = $res->GetNext()) {
            $this->products_structure[$product_db['PROPERTY_STARTVOLT_CODE_VALUE']][$product_db['ID']] = array(
                'ID' => $product_db['ID'],
                'STARTVOLT_CODE' => $product_db['PROPERTY_STARTVOLT_CODE_VALUE'],
            );
        }
        return $this->products_structure;
    }

    public function FindImages() {
        foreach ($this->products_structure as $products_key => $products) {
            foreach ($products as $product_id => $product) {
                $code = $product['STARTVOLT_CODE'];
                foreach ($this->tmp_image_structure as $key => $image) {
                    $name = $image['name'];
                    if (strpos($name, $code) !== false) {
                        if (empty($this->products_structure_after_find_image[$product_id])) {
                            $this->products_structure_after_find_image[$product_id] = array(
                                'ID' => $product_id,
                                'STARTVOLT_CODE' => $code,
                                'PHOTOS' => array(),
                            );
                        }
                        $this->products_structure_after_find_image[$product_id]['PHOTOS'][] = $image;
                    }
                }
            }
        }
        return $this->products_structure_after_find_image;
    }

    public function UpdateImages() {
        foreach ($this->products_structure_after_find_image as $product_id => $product) {
            $arr = array();
            $arr['PROPERTY_VALUES']['PHOTOS'] = $product['PHOTOS'];
            CIBlockElement::SetPropertyValuesEx($product_id, $this->PRODUCTS_IBLOCK_ID, $arr['PROPERTY_VALUES']);
        }
    }

    public function FinishImportImages() {
        //удаляем все временные файлы
        DeleteDirFilesEx($this->upload_path . $this->upload_path_images . 'tmp/');
        //DeleteDirFilesEx($this->upload_path . $this->upload_path_images . 'small/');
        DeleteDirFilesEx("/bitrix/cache/import_images/");
        $this->CheckImportImageFile();
    }

    public function ImportShops() {
        $this->import_error = '';
        //тут работа с кешем, результаты работы скрипта храним в кеше
        if (empty($_SESSION['IMPORT_STEP'])) {
            $_SESSION['IMPORT_STEP'] = 0;
        }
        $this->import_shops_actual_step = $_SESSION['IMPORT_STEP'];
        $obCache = new CPHPCache;
        for ($i = 0; $i < $this->import_shops_actual_step; $i++) {
            $cache_time = 360000;
            $cache_id = 'import_shops-' . $this->import_date . '-' . session_id() . '-' . $i;
            $cache_dir = "/import_shops/" . $this->import_date . '/' . session_id() . '/' . $i . '/';
            if ($obCache->InitCache($cache_time, $cache_id, $cache_dir)) {
                $res = $obCache->GetVars();
                $arResult = $res['arResult'];
                if (!empty($arResult['result'])) {
                    $return_property = $arResult['return_property'];
                    $this->$return_property = $arResult['result'];
                }
            } else {
                
            }
        }

        //смотрим на каком мы шаге
        $step = $this->import_shops_steps[$this->import_shops_actual_step];
        $method = $step['method'];
        if (method_exists($this, $method)) {
            $this->import_shops_steps[$this->import_shops_actual_step]['result'] = $this->$method();
            if (!$this->import_shops_steps[$this->import_shops_actual_step]['result']) {
                $_SESSION['IMPORT_STEP'] = count($this->import_shops_steps) + 1;
                return false;
            }
        } else {
            $_SESSION['IMPORT_STEP'] = count($this->import_shops_steps) + 1;
            return $_SESSION['IMPORT_STEP'];
        }
        //кешируем
        $cache_time = 360000;
        $cache_id = 'import_shops-' . $this->import_date . '-' . session_id() . '-' . $this->import_shops_actual_step;
        $cache_dir = "/import_shops/" . $this->import_date . '/' . session_id() . '/' . $this->import_shops_actual_step . '/';
        $obCache->StartDataCache($cache_time, $cache_id, $cache_dir);
        $obCache->EndDataCache(array("arResult" => $this->import_shops_steps[$this->import_shops_actual_step]));
        $_SESSION['IMPORT_STEP'] = $this->import_shops_actual_step + 1;

        return $_SESSION['IMPORT_STEP'];
    }

    //методы шагов импорта точек продаж
    public function SaveShopsFile() {
        $file = $this->shops_file;
        //сохраним файл во временную папку
        $tmp_path = $this->upload_path_shops . 'tmp';
        $info = Array("MODULE_ID" => $this->IMAGES_MODULE_ID);
        $arFILE = array_merge($file, $info);

        $fid = CFile::SaveFile($arFILE, $tmp_path);

        $import_shops_file = CFile::GetByID($fid)->GetNext();
        $this->import_shops_file = array(
            'ID' => $import_shops_file['ID'],
            'NAME' => $import_shops_file['ORIGINAL_NAME'],
            'PATH' => CFile::GetPath($import_shops_file['ID']),
            'DATE' => $import_shops_file['TIMESTAMP_X'],
        );

        return $this->import_shops_file;
    }

    public function ReadCsvShops() {
        global $APPLICATION;
        $path = $this->DOCUMENT_ROOT . $this->import_shops_file['PATH'];
        $this->import_shops_file_content = $APPLICATION->GetFileContent($path);
        return $this->import_shops_file_content;
    }

    public function ParseCsvCountries() {
        $this->countries_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_shops_file_content);
        $countries_rows = array();
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            //первую и сторую строки пропускаем - заголовки
            if ($row != 0 && $row != 1) {

                $value_array = explode($this->field_separator, $value);
                foreach ($value_array as $key => $value_value) {
                    $trim_value = trim(str_replace('"', '', $value_value));
                    //ищем столбюец со странами
                    if ($key == 2 && !in_array($trim_value, $countries_names) && $trim_value != '') {
                        $countries_names[] = $trim_value;
                        $countries_rows[] = array("CODE" => $this->translitName($trim_value),
                            "NAME" => str_replace('"', '', $trim_value)
                        );
                    }
                }
            }
        }
        $this->countries_rows = $countries_rows;

        return $this->countries_rows;
    }

    public function ParseCsvRegions() {
        $this->regions_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_shops_file_content);
        $regions_rows = array();
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            //первую и сторую строки пропускаем - заголовки
            if ($row != 0 && $row != 1) {

                $value_array = explode($this->field_separator, $value);
                foreach ($value_array as $key => $value_value) {
                    $trim_value = trim(str_replace('"', '', $value_value));
                    $this->regionCountry = '';
                    //ищем столбюец со странами
                    if ($key == 2) {
                        $this->regionCountry = $this->translitName($trim_value);
                        foreach ($this->countries_rows as $country_cache_id => $cache_country) {
                            if ($this->regionCountry == $cache_country['CODE']) {
                                $country_id = $cache_country['CODE'];
                                break;
                            }
                        }
                    }

                    if ($key == 3 && !in_array($trim_value, $regions_names) && $trim_value != '') {


                        $regions_names[] = $trim_value;
                        $regions_rows[] = array("CODE" => $this->translitName($trim_value),
                            "NAME" => str_replace('"', '', $trim_value),
                            "COUNTRY_ID" => $country_id
                        );
                    }
                }
            }
        }
        $this->regions_rows = $regions_rows;
        return $this->regions_rows;
    }

    public function ParseCsvCities() {
        $this->cities_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_shops_file_content);
        $cities_rows = array();
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            //первую и сторую строки пропускаем - заголовки
            if ($row != 0 && $row != 1) {

                $value_array = explode($this->field_separator, $value);
                foreach ($value_array as $key => $value_value) {
                    $trim_value = trim(str_replace('"', '', $value_value));
                    $this->cityRegion = '';
                    $this->cityCountry = '';
                    //ищем столбюец со странами
                    if ($key == 2) {
                        $this->cityCountry = $this->translitName($trim_value);
                        foreach ($this->countries_rows as $country_cache_id => $cache_country) {
                            if ($this->cityCountry == $cache_country['CODE']) {
                                $country_id = $cache_country['CODE'];
                                break;
                            }
                        }
                    }
                    //ищем столбюец с регионами
                    if ($key == 3) {
                        $this->cityRegion = $this->translitName($trim_value);
                        if (!empty($this->cityRegion)) {
                            foreach ($this->regions_rows as $region_cache_id => $cache_region) {
                                if ($this->cityRegion == $cache_region['CODE']) {
                                    $region_id = $cache_region['CODE'];
                                    break;
                                }
                            }
                        } else {
                            $region_id = $country_id;
                        }
                    }

                    //ищем столбюец с городами
                    if ($key == 4 && !in_array($trim_value, $cities_names) && $trim_value != '') {
                        $cities_names[] = $trim_value;
                        $cities_rows[] = array("CODE" => $this->translitName($trim_value),
                            "NAME" => str_replace('"', '', $trim_value),
                            "REGION_ID" => $region_id
                        );
                    }
                }
            }
        }
        $this->cities_rows = $cities_rows;
        return $this->cities_rows;
    }

    public function ParseCsvTypesShops() {

        $this->types_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_shops_file_content);
        $types_rows = array();
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            //первую и сторую строки пропускаем - заголовки
            if ($row != 0 && $row != 1) {

                $value_array = explode($this->field_separator, $value);
                foreach ($value_array as $key => $value_value) {
                    $trim_value = trim(str_replace('"', '', $value_value));

                    //ищем столбюец с городами
                    if ($key == 8 && !in_array($trim_value, $types_names) && $trim_value != '') {
                        $types_names[] = $trim_value;
                        $types_rows[] = array("CODE" => $this->translitName($trim_value),
                            "NAME" => str_replace('"', '', $trim_value),
                        );
                    }
                }
            }
        }
        $this->types_rows = $types_rows;
        return $this->types_rows;
    }

    public function ParseCsvShops() {
        $this->shops_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_shops_file_content);
        $shops_rows = array();
        $structure = array();
        $structure_scheme = array();
        foreach ($array as $row => $value) {
            //первую и сторую строки пропускаем - заголовки
            if ($row != 0 && $row != 1) {
                $cityShop = '';
                $value_array = explode($this->field_separator, $value);


                $this->cityShop = $this->translitName(trim(str_replace('"', '', $value_array[4])));
                if ($this->cityShop != '') {
                    foreach ($this->cities_rows as $city_cache_id => $cache_city) {
                        if (trim($this->cityShop) == $cache_city['CODE']) {
                            $cityShop = $cache_city['CODE'];
                            break;
                        }
                    }
                }
                $this->cityShop = '';

                $this->typeShop = $this->translitName($trim_value = trim(str_replace('"', '', $value_array[8])));
                foreach ($this->types_rows as $type_cache_id => $cache_type) {
                    if ($this->typeShop == $cache_type['CODE']) {
                        $typeShop = $cache_type['CODE'];
                        break;
                    }
                }


                $shops_rows[] = array("CODE" => $this->translitName($value_array[1]) . '-' . $this->translitName($value_array[3]) . '-' . $this->translitName($value_array[5]),
                    "NAME" => trim(str_replace('"', '', $value_array[1])),
                    "CITY" => $cityShop,
                    "ADDRESS" => trim(str_replace('"', '', $value_array[5])),
                    "PHONE" => trim(str_replace('"', '', $value_array[6])),
                    "SITE" => trim(str_replace('"', '', $value_array[7])),
                    "TYPE" => $typeShop,
                    "REGION" => $this->translitName(trim(str_replace('"', '', $value_array[3])))
                );
            }
        }
        $this->shops_rows = $shops_rows;
        return $this->shops_rows;
    }

    public function UpdateShops() {
        $shop_types = array(
            'diler' => array(
                'internet' => '8',
                'retail' => '7',
                'type' => '1'
            ),
            'distribyutor' => array(
                'internet' => '8',
                'retail' => '7',
                'type' => '5'
            ),
            'internet-magazin' => array(
                'internet' => '9',
                'retail' => '',
                'type' => '3'
            ),
            'optovik' => array(
                'internet' => '8',
                'retail' => '7',
                'type' => '2'
            ),
            'roznichnyy-magazin' => array(
                'internet' => '8',
                'retail' => '6',
                'type' => '4'
            ),
        );

        $arSelect = Array("ID", "NAME", "IBLOCK_ID", "ACTIVE", "CODE");
        $arFilter = Array("IBLOCK_ID" => 4);
        $res_shops = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        while ($shops_db = $res_shops->GetNext()) {
            $arLoadShopsArray = Array(
                "ACTIVE" => "N"
            );
            $active_shops[] = $shops_db["CODE"];
            $res = $this->el->Update($shops_db['ID'], $arLoadShopsArray);
        }


        $countries = $this->countries_rows;
        $regions = $this->regions_rows;
        $cities = $this->cities_rows;
        $shops = $this->shops_rows;

        //Что уже есть в базе из разделов
        $db_items = GetIBlockSectionList(5, false, Array("sort" => "asc"));
        $active_country = array();
        while ($items = $db_items->GetNext()) {
            $active_country[] = $items['CODE'];
        }
        //Что уже есть в базе из городов
        $result = CIBlockElement::GetList(
                        Array("ID" => "DESC"), Array("IBLOCK_ID" => 5, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"), false, false, Array()
        );
        while ($element = $result->GetNextElement()) {
            $arField = $element->GetFields();
            $active_cities[] = $arField["CODE"];
        }
        //Что уже есть в базе из магазинов
        //Пишем Страны
        foreach ($countries as $key => $country) {
            if (!in_array($country["CODE"], $active_country)) {
                $arFields = Array(
                    "ACTIVE" => "Y",
                    // "IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
                    "IBLOCK_ID" => 5,
                    "NAME" => $country["NAME"],
                    "CODE" => $country["CODE"],
                    "SORT" => $key,
                );
                $res = $this->bs->Add($arFields);
            }
        }

        //Пишем Регионы
        foreach ($regions as $key => $region) {
            //Что в базе

            $db_items = GetIBlockSectionList(5, false, Array("sort" => "asc"), false, array("CODE" => $region["COUNTRY_ID"]));
            $active_country = array();
            while ($items = $db_items->GetNext()) {
                $active_country = $items['ID'];
            }
            if (!in_array($region["CODE"], $active_country)) {
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_SECTION_ID" => $active_country,
                    "IBLOCK_ID" => 5,
                    "NAME" => $region["NAME"],
                    "CODE" => $region["CODE"],
                    "SORT" => $key,
                );
                // print_r($arFields);   
                $res = $this->bs->Add($arFields);
            }
        }

        //Пишем города
        foreach ($cities as $key => $city) {
            //Что в базе

            $db_items = GetIBlockSectionList(5, false, Array("sort" => "asc"), false, array("CODE" => $city["REGION_ID"]));
            $active_country = array();
            while ($items = $db_items->GetNext()) {
                $active_region = $items['ID'];
            }

            //Есть ли уже такой город  
            if (!in_array($city["CODE"], $active_cities)) {
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_SECTION_ID" => $active_region,
                    "IBLOCK_ID" => 5,
                    "NAME" => $city["NAME"],
                    "CODE" => $city["CODE"],
                    "SORT" => $key,
                );
                $res = $this->el->Add($arFields);
            } else {
                $result = CIBlockElement::GetList(
                                Array("ID" => "DESC"), Array("IBLOCK_ID" => 5, "CODE" => $city["CODE"]), false, false, Array("ID")
                );
                while ($element = $result->GetNextElement()) {
                    $arField = $element->GetFields();
                    $update_city = $arField["ID"];
                }
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_SECTION_ID" => $active_region,
                    "IBLOCK_ID" => 5,
                    "NAME" => $city["NAME"],
                    "CODE" => $city["CODE"],
                    "SORT" => $key,
                );
                $res = $this->el->Update($update_city, $arFields);
            }
        }

        //Пишем точки продаж
        foreach ($shops as $key => $shop) {

            /*
             * $internet - вкладки Розница\Оптом / Интернет-магазин
             * Розница - 8
             * Интернет-магазин - 9
             * 
             * $retail - Выпадающий список В Розницу/Оптом
             * Розница - 6
             * Оптом - 7
             * 
             * $type - Тип точки (Дилер, Оптовик, Интернет-магазин, Розничный магазин, Дистрибьютор)
             * Дилер - 1;
             * Оптовик - 2
             * Интернет-магазин - 3
             * Розничный магазин - 4
             * Дистрибьютор - 5
             */


            //определяем тип
            foreach ($shop_types as $find_type => $db_data) {
                $pos = strpos($shop["TYPE"],$find_type);
                if ($pos !== false) {
                    $internet = $shop_types[$find_type]['internet'];
                    $retail = $shop_types[$find_type]['retail'];
                    $type = $shop_types[$find_type]['type'];
                    break;
                }
            }
            
//            switch ($shop["TYPE"]) {
//                case 'diler': $internet = '8';
//                    $retail = '7';
//                    $type = '1';
//                    break;       //Дилер
//                case 'distribyutor': $internet = '8';
//                    $retail = '7';
//                    $type = '5';
//                    break;       //Дистрибьютор
//                case 'internet-magazin': $internet = '9';
//                    $retail = '';
//                    $type = '3';
//                    break;       //Интернет-магазин
//                case 'optovik': $internet = '8';
//                    $retail = '7';
//                    $type = '2';
//                    break;       //Оптовик
//                case 'roznichnyy-magazin': $internet = '8';
//                    $retail = '6';
//                    $type = '4';
//                    break;       //Розничный магазин
//            }
            $shop_region = 0;
            if ($shop["REGION"] != '') {
                $db_items = GetIBlockSectionList(5, false, Array("sort" => "asc"), false, array("CODE" => $shop["REGION"]));
                while ($items = $db_items->GetNext()) {
                    $shop_region = $items['ID'];
                }
            }
            if ($internet != 9) {
                //Что уже есть в базе из городов
                if ($shop["CITY"] != '') {
                    $result = CIBlockElement::GetList(
                                    Array("ID" => "DESC"), Array("IBLOCK_ID" => 5, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "CODE" => $shop["CITY"]), false, false, Array("ID")
                    );
                    $active_city = '';
                    while ($element = $result->GetNextElement()) {
                        $arField = $element->GetFields();
                        $active_city = $arField["ID"];
                    }
                } else {
                    $active_city = '';
                }
            } else {
                $active_city = '';
            }

            $prop = array("CITY" => $active_city,
                "PHONE" => $shop["PHONE"],
                "SITE" => $shop["SITE"],
                "ADDRESS" => $shop["ADDRESS"],
                "INTERNET" => $internet,
                "RETAIL" => $retail,
                "TYPE" => $type,
                "COUNTRY" => $shop_region);
            if (!in_array($shop["CODE"], $active_shops)) {
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => 4,
                    "NAME" => $shop["NAME"],
                    "CODE" => $shop["CODE"],
                    "PROPERTY_VALUES" => $prop
                );
                //  print_r($arFields);   
                $res = $this->el->Add($arFields);
            } else {
                $result = CIBlockElement::GetList(
                                Array("ID" => "DESC"), Array("IBLOCK_ID" => 4, "CODE" => $shop["CODE"]), false, false, Array()
                );
                while ($element = $result->GetNextElement()) {
                    $arField = $element->GetFields();
                    $id_update_shop = $arField["ID"];
                }


                CIBlockElement::SetPropertyValuesEx($id_update_shop, 4, $prop);
                $arLoadShopsArray = Array(
                    "ACTIVE" => "Y"
                );
                $res = $this->el->Update($id_update_shop, $arLoadShopsArray);
            }
        }
    }

}
