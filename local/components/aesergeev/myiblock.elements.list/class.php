<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Application,
    \Bitrix\Main\Loader,
    \Bitrix\Iblock\TypeTable,
    \Bitrix\Iblock\IblockTable,
    \Bitrix\Iblock\SectionTable,
    \Bitrix\Iblock\ElementTable,
    \Bitrix\Iblock\ElementPropertyTable,
    \Bitrix\Main\Data\Cache;

Loader::includeModule('iblock');

class MYIblockElementsList extends CBitrixComponent {
    public function onPrepareComponentParams($arParams)
    {
        if (!isset($arParams['PROP_TAGS_NAME'])) {
            $arParams['PROP_TAGS_NAME'] = 'TAGS';
        } else {
            $arParams['PROP_TAGS_NAME'] = trim($arParams['PROP_TAGS_NAME']);
        }

        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

        return $arParams;
    }

    function executeComponent() {
        $arParams = $this->arParams;

        // Тегированный кеш.
        $cache = Cache::createInstance(); // Служба кеширования
        $taggedCache = Application::getInstance()->getTaggedCache(); // Служба пометки кеша тегами
        $cachePath = 'MyIblockElementsList';
        $cacheTtl = 86400 * 7;
        $cacheKey = md5(serialize($arParams));

        if ($cache->initCache($cacheTtl, $cacheKey, $cachePath)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $taggedCache->startTagCache($cachePath);

            // Получаем ID свойства тегов
            $db_prop_tags = \Bitrix\Iblock\PropertyTable::getList([
                'select' => ['ID'],
                'filter' => ['CODE' => $arParams['PROP_TAGS_NAME']],
                'limit' => 1,
            ])->fetch();
            $propTagsID = intval($db_prop_tags['ID']);

            // Если не получилось получить ID свойства тегов, то не кешируем.
            if (!$propTagsID) {
                $taggedCache->abortTagCache();
                $cache->abortDataCache();
                return;
            }

            //
            // Получаем список разделов и кол-во элементов в них.
            // Исключаем пустые разделы.
            //
            $select = ['ID', 'NAME', 'countElements'];
            $filter = [
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y',
                '!countElements' => 0,
            ];
            $runtime = [
                'elements' => [
                    'data_type' =>"Bitrix\Iblock\ElementTable",
                    'reference' => [
                        '=this.IBLOCK_ID' => 'ref.IBLOCK_ID',
                        '=this.ID' => 'ref.IBLOCK_SECTION_ID',
                        '=this.ACTIVE' => 'ref.ACTIVE',
                    ],
                ],
                'countElements' => [
                    'data_type' => 'integer',
                    'expression' => ['count(%s)', 'elements.ID']
                ]
            ];
            $db_res = SectionTable::getList([
                'select' => $select,
                'filter' => $filter,
                'runtime' => $runtime,
            ]);

            while ($sectionRes = $db_res->fetch()) {
                $arResult[ $sectionRes['ID'] ]['NAME'] = $sectionRes['NAME'];

                $db_elem_res = ElementTable::getList([
                    'select' => ['ID', 'NAME'],
                    'filter' => [
                        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                        'IBLOCK_SECTION_ID' => $sectionRes['ID'],
                        'ACTIVE' => 'Y',
                    ]
                ]);

                // Получаем значения свойства Теги для каждого элемента.
                while ($arItem = $db_elem_res->fetch()) {
                    $arTags = [];
                    $propRes = ElementPropertyTable::getList([
                        'select' => ['ID', 'VALUE'],
                        "filter" => [
                            'IBLOCK_PROPERTY_ID' => $propTagsID,
                            "IBLOCK_ELEMENT_ID" => $arItem["ID"],
                        ],
                    ]);
                    while ($prop = $propRes->fetch())
                        $arTags[] = $prop['VALUE'];

                    $arResult[ $sectionRes['ID'] ]['ITEMS'][] = [
                        'NAME' => $arItem['NAME'],
                        'TAGS' => $arTags,
                    ];
                }
            }

            $taggedCache->registerTag('iblock_id_' . $arParams['IBLOCK_ID']);

            // Всё хорошо, записываем кеш
            $taggedCache->endTagCache();
            $cache->endDataCache($arResult);
        }

        $this->arResult = $arResult;
        $this->includeComponentTemplate();
    }
}