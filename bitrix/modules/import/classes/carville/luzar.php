<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

class ImportProductsLuzar {

    public function __construct() {
        $this->DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $this->upload_path = '/upload/';
        $this->products_structure = array();
        $this->products_structure_after_find_image = array();
        $this->import_error = '';
        $this->start_import_path = '/bitrix/tools/import/start_import_carville_luzar.php';
        $this->clear_cache_path = '/bitrix/tools/import/clear_import_carville_cache.php';
        $this->CARS_IBLOCK_ID = 11;
        $this->PRODUCTS_IBLOCK_ID = 6;
        $this->PRODUCTS_MODULE_ID = 'import_products_carville_luzar';
        $this->SECTIONS_MODULE_ID = 'import_sections_carville_luzar';
        $this->LOG_PRODUCTS_MODULE_ID = 'import_carville_luzar_products_log';
        $this->LOG_SECTIONS_MODULE_ID = 'import_carville_luzar_sections_log';

        $this->import_products_file = array();
        $this->import_sections_file = array();
        $this->import_products_file_content = '';
        $this->import_sections_file_content = '';
        $this->catalog_flag = '::CATALOGUE::';
        $this->header_flag = 'HEADER';
        $this->product_flag = '::PRODUCT::';
        $this->field_separator = ';';
        $this->sub_category_separator = '::';
        $this->line = "\n";
        $this->combine_code_category = 'Оригинал';
        $this->combine_code_products = 'Фирменное наим-ние товара';
        $this->category_name = 'Категория';
        $this->db_fields_products = array(
            '№' => '',
            'Полное наименование' => 'NAME',
            'URL' => 'CODE',
            'Краткое наименование' => '',
            'Наша цена (опт)' => '',
            'Интервал замены' => '',
            'Система автомобиля' => '',
            'Категория' => '',
            'Тип' => 'PROPERTY_TYPE',
            'OEM номер' => 'PROPERTY_OEM_NUMBER',
            'Фирменное наим-ние товара' => 'PROPERTY_STARTVOLT_CODE',
            'Описание конструкции' => 'PREVIEW_TEXT',
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
            '№' => '',
            'Наименование' => '',
            'Оригинал' => '',
            'Применяется для автомобилей' => '',
            '№ по каталогу' => '',
            'Розн.' => 'PROPERTY_PRICE',
            'Новинка' => 'PROPERTY_NEW_PROD',
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
        $this->count_of_import_products = 100; //импорт по 100 товаров
        $this->count_of_import_cars = 100; //импорт по 100 автомобилей
        $this->import_steps = array(
            '0' => array(
                'method' => 'ReadCsvProducts',
                'result' => array(),
                'return_property' => 'import_products_file_content',
                'steps' => 0,
                'description' => 'Загрузка файла с товарами...',
            ),
            '1' => array(
                'method' => 'ReadCsvSections',
                'result' => array(),
                'return_property' => 'import_sections_file_content',
                'steps' => 0,
                'description' => 'Загрузка файла с категориями...',
            ),
            '2' => array(
                'method' => 'ParseCsvProducts',
                'result' => array(),
                'return_property' => 'products_rows',
                'steps' => 0,
                'description' => 'Чтение файла с товарами...',
            ),
            '3' => array(
                'method' => 'ParseCsvSections',
                'result' => array(),
                'return_property' => 'sections_rows',
                'steps' => 0,
                'description' => 'Чтение файла с категориями...',
            ),
            '4' => array(
                'method' => 'CombineTables',
                'result' => array(),
                'return_property' => 'structure',
                'steps' => 0,
                'description' => 'Объединение таблиц...',
            ),
            '5' => array(
                'method' => 'MakeCarsStructure',
                'result' => array(),
                'return_property' => 'structure_cars',
                'steps' => 0,
                'description' => 'Формирование списка автомобилей...',
            ),
            '6' => array(
                'method' => 'MakeCars',
                'result' => array(),
                'return_property' => 'structure_db',
                'steps' => 100,
                'description' => 'Импорт автомобилей...',
            ),
            '7' => array(
                'method' => 'ImportSections',
                'result' => array(),
                'return_property' => 'structure_after_sections_import',
                'steps' => 0,
                'description' => 'Импорт категорий...',
            ),
            '8' => array(
                'method' => 'PrepareImportProducts',
                'result' => array(),
                'return_property' => 'structure_for_products_import',
                'steps' => 0,
                'description' => 'Подготовка к импорту товаров...',
            ),
            '9' => array(
                'method' => 'ImportProducts',
                'result' => array(),
                'return_property' => 'structure_after_products_import',
                'steps' => 100,
                'description' => 'Импорт товаров....',
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
            $cache_id = 'import_carville-' . $this->import_date . '-' . session_id() . '-' . $i;
            $cache_dir = "/import_carville/" . $this->import_date . '/' . session_id() . '/' . $i . '/';
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

        //если этот шаг делается по шагам, то загружаем предыдущий результат
        if (!empty($this->import_steps[$this->import_actual_step]['steps'])) {
            $cache_time = 360000;
            $cache_id = 'import_carville-' . $this->import_date . '-' . session_id() . '-' . $this->import_actual_step;
            $cache_dir = "/import_carville/" . $this->import_date . '/' . session_id() . '/' . $this->import_actual_step . '/';
            if ($obCache->InitCache($cache_time, $cache_id, $cache_dir)) {
                $res = $obCache->GetVars();
                $arResult = $res['arResult'];
                if (!empty($arResult['result'])) {
                    $return_property = $arResult['return_property'];
                    $this->$return_property = $arResult['result'];
                    if ($arResult['result']['success'] == 0) {
                        //убили кеш
                        DeleteDirFilesEx("/bitrix/cache" . $cache_dir);
                    }
                }
            }
        }

        if (!empty($this->structure_after_products_import)) {
            if ($this->structure_after_products_import['success'] == 1) {
                $_SESSION['IMPORT_STEP'] = $this->import_actual_step + 1;
                //убиваем весь кеш
                DeleteDirFilesEx("/bitrix/cache/import_carville/");
                DeleteDirFilesEx("/bitrix/cache/products.filter.carville/");
                DeleteDirFilesEx("/bitrix/cache/products.autos.carville/");
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
        $cache_id = 'import_carville-' . $this->import_date . '-' . session_id() . '-' . $this->import_actual_step;
        $cache_dir = "/import_carville/" . $this->import_date . '/' . session_id() . '/' . $this->import_actual_step . '/';
        $obCache->StartDataCache($cache_time, $cache_id, $cache_dir);
        $obCache->EndDataCache(array("arResult" => $this->import_steps[$this->import_actual_step]));

        //записываем следующий щаг
        if (empty($this->import_steps[$this->import_actual_step]['steps']) ||
                (!empty($this->import_steps[$this->import_actual_step]['steps']) && $this->import_steps[$this->import_actual_step]['result']['success'] == 1)) {
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

    //Вспомогательный метод, переводит текст в нижний регистр с первой заглавной буквой
    function LowerName($name) {
        $name = ToLower($name);
        $name = ToUpper(mb_substr($name, 0, 1)) . mb_substr($name, 1, mb_strlen($name));
        return $name;
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
        if (!empty($this->structure_cars['CODE'][$CODE])) {
            return $this->structure_cars['CODE'][$CODE]['ID'];
        } else {
            $arFields = Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $this->CARS_IBLOCK_ID,
                "NAME" => $mark_code,
                "CODE" => $CODE,
            );
            $mark_id = $this->bs->Add($arFields);
            $this->structure_cars['CODE'][$CODE] = array(
                'ID' => $mark_id,
                'MODELS' => array(),
            );
            $this->structure_cars['ID'][$mark_id] = array(
                'CODE' => $CODE,
                'MODELS' => array(),
            );
            return $mark_id;
        }
    }

    public function CheckCarModel($mark_id, $model) {
        $model['NAME'] = trim($model['NAME']);
        $CODE = $this->translitName($model['NAME']);
        $model_id = false;
        if (!empty($this->structure_cars['ID'][$mark_id]['MODELS'][$CODE])) {
            $models = $this->structure_cars['ID'][$mark_id]['MODELS'][$CODE];
            foreach ($models as $model_key => $structure_model) {
                $arFields = $structure_model['arFields'];
                $arProps = $structure_model['arProps'];
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
                            foreach ($value as $value_key => $multy_value) {                             
                                if (!in_array($multy_value, $arProps[$PROPERTY_CODE]['VALUE'])) {
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
            if ($model_id) {
                $model_db = CIBlockElement::GetByID($model_id)->GetNextElement();
                $arFields = $model_db->GetFields();
                $arProps = $model_db->GetProperties();
                $this->structure_cars['CODE'][$this->structure_cars['ID'][$mark_id]['CODE']]['MODELS'][$arFields['CODE']][] = array(
                    'arFields' => $arFields,
                    'arProps' => $arProps,
                );
                $this->structure_cars['ID'][$mark_id]['MODELS'][$arFields['CODE']][] = array(
                    'arFields' => $arFields,
                    'arProps' => $arProps,
                );
            }
        }
        return $model_id;
    }

    public function CheckCategory($parent_id, $category, $sort) {
        $CODE = $this->translitName($category['NAME']);
        $arFilter = Array('IBLOCK_ID' => $this->PRODUCTS_IBLOCK_ID, 'CODE' => $CODE);
        $db_list = CIBlockSection::GetList(Array('SORT' => 'ASC', 'CREATED' => 'ASC'), $arFilter, true);
        if ($category_db = $db_list->GetNext()) {
            $arFields = Array(
                "ACTIVE" => "Y",
                "DESCRIPTION" => $category['DESCRIPTION'],
                "DESCRIPTION_TYPE" => 'html'
            );
            $res = $this->bs->Update($category_db['ID'], $arFields);
            //также активируем все родительские категории
            $this->ActiveParentCategory($category_db);
            return $category_db['ID'];
        } else {
            $arFields = Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID,
                "NAME" => $category['NAME'],
                "CODE" => $CODE,
                "DESCRIPTION" => $category['DESCRIPTION'],
                "DESCRIPTION_TYPE" => 'html'
            );

            $category_id = $this->bs->Add($arFields);
            return $category_id;
        }
    }

    public function ActiveParentCategory($category) {
        if (empty($category['IBLOCK_SECTION_ID'])) {
            return false;
        }
        $arFields = Array(
            "ACTIVE" => "Y",
        );
        $res = $this->bs->Update($category['IBLOCK_SECTION_ID'], $arFields);
        $parent_category = CIBlockSection::GetByID($category['IBLOCK_SECTION_ID'])->GetNext();
        return $this->ActiveParentCategory($parent_category);
    }

    public function ImportOneProduct($category_id, $product, $sort) {
        $CODE = $this->translitName($product['CODE']) . '-' . $this->translitName($product['PROPERTY_VALUES']['STARTVOLT_CODE']);
        $arSelect = Array();
        $arFilter = Array("IBLOCK_ID" => $this->PRODUCTS_IBLOCK_ID, 'CODE' => $CODE);
        $res = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
        if ($product_db = $res->GetNext()) {
            //SPECIFICATIONS
            CIBlockElement::SetPropertyValueCode($product_db['ID'], 'SPECIFICATIONS', $product['PROPERTY_VALUES']['SPECIFICATIONS']);

            //STARTVOLT_CODE
            CIBlockElement::SetPropertyValueCode($product_db['ID'], 'STARTVOLT_CODE', $product['PROPERTY_VALUES']['STARTVOLT_CODE']);

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
            $value = trim($value);
            if (!empty($value)) {
                $value_array = explode($this->field_separator, $value);
                foreach ($value_array as $key => $value_value) {
                    $value_value = str_replace('"', '', $value_value);
                    
                    //первая строка - это заголовки
                    if ($k == 0) {
                        $structure_scheme[$key] = $value_value;
                    } else {
                        $structure[$k - 1][$structure_scheme[$key]] = $value_value;
                    }
                }
                $k++;
            }
        }
        $_structure = array();
        foreach ($structure as $key => $product) {
            $_structure[$product[$this->combine_code_products]][] = $product;
        }
        $this->products_rows = $_structure;

        return $this->products_rows;
    }

    public function ParseCsvSections() {

        /*
         * Тут все изменилось. категории берез из файла с продуктами, вот такие дела. Отсюда по сути только цена.
         * раньше было ParseCsvSections_old();
         */
        $this->sections_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_sections_file_content);

        $structure_scheme = array();
        $structure = array();
        $category_number = 1;
        $product_number = 0;
        foreach ($array as $key => $line) {
            $values = explode($this->field_separator, $line);
            if ($key == 0) { //первая строка - заголовок
                foreach ($values as $structure_scheme_key => $structure_scheme_value) {
                    $structure_scheme_key = str_replace('"', '', $structure_scheme_key);
                    $structure_scheme_value = str_replace('"', '', $structure_scheme_value);
                    $values[$structure_scheme_key] = str_replace('"', '', $values[$structure_scheme_key]);
                    $structure_scheme[$structure_scheme_key] = trim($values[$structure_scheme_key]);
                }
            } else {
                //первое значение - всегда номер, если не так, то либо пустое значение, либо название категории
                $number = (int) $values[0];
                $name = trim($values[0]);
                if (empty($number) && !empty($name)) {
                    $category_number++;
                } elseif (!empty($number)) { //значит это товар
                    $structure[$product_number] = array();
                    foreach ($structure_scheme as $structure_scheme_key => $structure_scheme_value) {
                        $structure_scheme_key = str_replace('"', '', $structure_scheme_key);
                        $values[$structure_scheme_key] = str_replace('"', '', $values[$structure_scheme_key]);
                        $structure[$product_number][$structure_scheme_value] = trim($values[$structure_scheme_key]);
                    }
                    $product_number++;
                }
            }
        }
        $this->sections_rows = $structure; 
        return $this->sections_rows;
    }

    public function ParseCsvSections_old() {

        /*
         * Тут все изменилось. категории берез из файла с продуктами, вот такие дела. Отсюда по сути только цена.
         * Теперь стало ParseCsvSections();
         */
        $this->sections_rows = array();
        $LINEEND = "\n";
        $array = explode($LINEEND, $this->import_sections_file_content);

        $structure_scheme = array();
        $structure = array();
        $category_number = 1;
        $product_number = 0;
        foreach ($array as $key => $line) {
            $values = explode($this->field_separator, $line);
            if ($key == 0) { //первая строка - заголовок
                foreach ($values as $structure_scheme_key => $structure_scheme_value) {
                    $structure_scheme[$structure_scheme_key] = trim($values[$structure_scheme_key]);
                }
            } else {
                //первое значение - всегда номер, если не так, то либо пустое значение, либо название категории
                $number = (int) $values[0];
                $name = trim($values[0]);
                if (empty($number) && !empty($name)) {
                    $structure[$category_number] = array(
                        'NAME' => $this->LowerName($name),
                        'PRODUCTS' => array()
                    );
                    $category_number++;
                } elseif (!empty($number)) { //значит это товар
                    $structure[$category_number - 1]['PRODUCTS'][$product_number] = array();
                    foreach ($structure_scheme as $structure_scheme_key => $structure_scheme_value) {
                        $structure[$category_number - 1]['PRODUCTS'][$product_number][$structure_scheme_value] = trim($values[$structure_scheme_key]);
                    }
                    $product_number++;
                }
            }
        }
        foreach ($structure as $structure_number => $category) {
            if (empty($category['PRODUCTS'])) {
                unset($structure[$structure_number]);
            }
        }
        $this->sections_rows = $structure;
        return $this->sections_rows;
    }

    public function CombineTables() {
        
        $this->structure = array();

        foreach ($this->sections_rows as $_product_key => $product) {
            $products_rows = $this->products_rows[$product[$this->combine_code_category]];
            
            $db_product = array();
            if (!empty($products_rows)) {                
                foreach ($products_rows as $__key_for_cat => $products_rows_value_for_cat) {
                    $category_name = $this->LowerName($products_rows_value_for_cat[$this->category_name]);
                    if (empty($structure[$category_name])) {
                        $structure[$category_name] = array(
                            'NAME' => $category_name,
                            'PRODUCTS' => array(),
                        );
                    }
                }

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
                $structure[$category_name]['PRODUCTS'][$_product_key] = $_db_product;
            }
        }
        $category_number = 1;
        $product_number = 1;
        foreach ($structure as $category_name => $category) {
            $this->structure[$category_number] = $category;
            $this->structure[$category_number]['PRODUCTS'] = array();
            foreach($category['PRODUCTS'] as $product_key=>$product){
                $this->structure[$category_number]['PRODUCTS'][$product_number] = $product;
                $product_number++;
            }
            $category_number++;
        }
        return $this->structure;
    }

    public function MakeCarsStructure() {
        $structure = array();
        $arSelect = Array();
        $arFilter = Array('IBLOCK_ID' => $this->CARS_IBLOCK_ID, 'GLOBAL_ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1);
        $db_list = CIBlockSection::GetList(Array('SORT' => 'ASC', 'CREATED' => 'ASC'), $arFilter, true);
        while ($mark = $db_list->GetNext()) {
            $structure['CODE'][$mark['CODE']] = array(
                'ID' => $mark['ID'],
                'MODELS' => array(),
            );
            $structure['ID'][$mark['ID']] = array(
                'CODE' => $mark['CODE'],
                'MODELS' => array(),
            );
            $arFilter_model = Array("IBLOCK_ID" => $this->CARS_IBLOCK_ID, "ACTIVE" => "Y", 'SECTION_ID' => $mark['ID']);
            $res_model = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter_model, false, false, $arSelect);
            while ($model_db = $res_model->GetNextElement()) {
                $arFields = $model_db->GetFields();
                $arProps = $model_db->GetProperties();
                $structure['CODE'][$mark['CODE']]['MODELS'][$arFields['CODE']][] = array(
                    'arFields' => $arFields,
                    'arProps' => $arProps,
                );
                $structure['ID'][$mark['ID']]['MODELS'][$arFields['CODE']][] = array(
                    'arFields' => $arFields,
                    'arProps' => $arProps,
                );
            }
        }
        $this->structure_cars = $structure;
        return $this->structure_cars;
    }

    public function MakeCars() {
        if (empty($this->structure_db)) {
            $this->structure_db = array(
                'structure' => $this->structure,
                'category_step' => 1,
                'product_step' => 1,
                'process_step' => 0,
                'success' => 0,
            );
        }

        //импорт по $this->import_steps[$this->import_actual_step]['steps'] товаров
        $product_count = 0;
        foreach ($this->structure_db['structure'] as $category_number => $category) {
            if ($category_number == $this->structure_db['category_step']) {
                foreach ($category['PRODUCTS'] as $product_code => $product) {
                    if ($product_code == $this->structure_db['product_step']) {
                        foreach ($product['CARS'] as $key => $car) {
                            foreach ($car as $mark_name => $mark) {
                                $mark_name = trim($mark_name);
                                $mark_id = $this->CheckCarMark($mark_name);
                                foreach ($mark['MODELS'] as $model_name => $model) {
                                    $model_id = $this->CheckCarModel($mark_id, $model);
                                    if (!in_array($model_id, $this->structure_db['structure'][$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'])) {
                                        $this->structure_db['structure'][$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'][] = $model_id;
                                    }
                                }
                            }
                        }
                        if (empty($this->structure_db['structure'][$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'])) {
                            $this->structure_db['structure'][$category_number]['PRODUCTS'][$product_code]['PROPERTY_VALUES']['CARS'] = false;
                        }
                        unset($this->structure_db['structure'][$category_number]['PRODUCTS'][$product_code]['CARS']);
                        $product_count++;
                        if ($product_count == $this->import_steps[$this->import_actual_step]['steps']) {
                            $this->structure_db['category_step'] = $category_number;
                            $this->structure_db['product_step'] = $product_code + 1;
                            return $this->structure_db;
                        }
                        $this->structure_db['product_step'] = $product_code + 1;
                        $this->structure_db['process_step'] = $this->structure_db['process_step'] + 1;
                    }
                    $this->structure_db['category_step'] = $category_number + 1;
                }
            }
        }
        if (empty($this->structure_db['structure'][$this->structure_db['category_step']])) {
            $this->structure_db['success'] = 1;
        }
        return $this->structure_db;
    }

    public function ImportSections() {
        $this->structure_after_sections_import = $this->structure_db['structure'];
        foreach ($this->structure_after_sections_import as $category_number => $category) {

            $category_id = $this->CheckCategory(false, $category, $category_number);
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
                /*
                  if (!empty($product['PROPERTY_VALUES']['POPULAR_PROD'])) {
                  $POPULAR_values = $this->getSelect('POPULAR_PROD', $this->PRODUCTS_IBLOCK_ID);
                  $product['PROPERTY_VALUES']['POPULAR_PROD'] = $POPULAR_values[0]['ID'];
                  } else {
                  $product['PROPERTY_VALUES']['POPULAR_PROD'] = '';
                  }
                 * 
                 */

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

                /*
                  $OPTION_TEXT = $product['PROPERTY_VALUES']['OPTIONS'];
                  $product['PROPERTY_VALUES']['OPTIONS'] = Array("VALUE" => Array("TEXT" => $OPTION_TEXT, "TYPE" => "html"));
                  $WARRANTY_TEXT = $product['PROPERTY_VALUES']['WARRANTY'];
                  $product['PROPERTY_VALUES']['WARRANTY'] = Array("VALUE" => Array("TEXT" => $WARRANTY_TEXT, "TYPE" => "html"));
                 * 
                 */

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
            $this->structure_after_products_import['process_step'] = 0;
            $this->structure_after_products_import['success'] = 0;
        }

        //импорт по $this->count_of_import_products товаров
        $import_product = 0;
        foreach ($this->structure_after_products_import['structure'] as $category_number => $category) {
            if ($category_number == $this->structure_after_products_import['category_step']) {
                foreach ($category['PRODUCTS'] as $key_product => $product) {
                    if ($key_product == $this->structure_after_products_import['product_step']) {
                        if (!empty($product['NAME'])) {
                            if (is_array($product['NAME'])) {
                                foreach ($product['NAME'] as $product_name_key => $product_name_value) {
                                    $_product = $product;
                                    unset($_product['NAME']);
                                    unset($_product['CODE']);
                                    $_product['NAME'] = $product_name_value;
                                    $_product['CODE'] = $product['CODE'][$product_name_key];
                                    $this->ImportOneProduct($category['ID'], $_product, $key_product);
                                }
                            } else {
                                $this->ImportOneProduct($category['ID'], $product, $key_product);
                            }
                        }
                        $import_product++;
                        if ($import_product == $this->count_of_import_products) {
                            $this->structure_after_products_import['category_step'] = $category_number;
                            $this->structure_after_products_import['product_step'] = $key_product + 1;
                            return $this->structure_after_products_import;
                        }
                        $this->structure_after_products_import['product_step'] = $key_product + 1;
                        $this->structure_after_products_import['process_step'] = $this->structure_after_products_import['process_step'] + 1;
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

}
