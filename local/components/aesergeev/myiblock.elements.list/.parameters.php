<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader,
    \Bitrix\Iblock\TypeTable,
    \Bitrix\Iblock\IblockTable,
    \Bitrix\Main\Localization\Loc;

Loader::includeModule('iblock');


$prepareTypeTable = TypeTable::getList([
    'select' => ['*', 'LANG_MESSAGE'],
    'filter' => ['=LANG_MESSAGE.LANGUAGE_ID' => 'ru'],
    'order' => ['SORT' => 'ASC']
])->fetchAll();

$arTypesEx['-'] = ' ';
foreach($prepareTypeTable as $item)
	$arTypesEx[ $item['ID'] ] = '[' . $item['ID'] . ']' . ' ' . $item['IBLOCK_TYPE_LANG_MESSAGE_NAME'];


$arIBlocks = [];
$db_iblock = IblockTable::getList([
    'order' => ['SORT' => 'ASC'],
    'filter' => [
        'IBLOCK_TYPE_ID' => ($arCurrentValues["IBLOCK_TYPE"] != "-"?$arCurrentValues["IBLOCK_TYPE"] : '')
    ],
]);
while($arRes = $db_iblock->Fetch())
    $arIBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];

$arComponentParameters = array(
	"GROUPS" => [],
	"PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage('PARAM_AES_ELEMENTS_LIST_IBLOCK_TYPE'),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage('PARAM_AES_ELEMENTS_LIST_IBLOCK_ID'),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		],
        "PROP_TAGS_NAME" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage('PARAM_AES_ELEMENTS_LIST_TAGS_NAME'),
            "TYPE" => "STRING",
            "DEFAULT" => 'TAGS',
        ],
	]
);