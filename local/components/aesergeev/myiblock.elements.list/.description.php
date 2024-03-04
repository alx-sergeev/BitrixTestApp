<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage('COMP_AES_MYIBLOCK_ELEMENTS_LIST_NAME'),
    'DESCRIPTION' => Loc::getMessage('COMP_AES_MYIBLOCK_ELEMENTS_LIST_DESC'),
    'PATH' => [
        'ID' => Loc::getMessage('COMP_AES_MYIBLOCK_ELEMENTS_LIST_PATH_ID'),
        'NAME' => Loc::getMessage('COMP_AES_MYIBLOCK_ELEMENTS_LIST_PATH_NAME'),
        'SORT' => 1100,
    ],
];