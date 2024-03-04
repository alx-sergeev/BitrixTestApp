<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?foreach ($arResult as $arSect):?>
<ol>
<li><?=$arSect['NAME'];?></li>
    <ol>
        <?foreach ($arSect['ITEMS'] as $arItem):?>
            <li><?=$arItem['NAME'] . ' (' . implode(', ', $arItem['TAGS']) . ')';?></li>
        <?endforeach;?>
    </ol>
</ol>
<?endforeach;?>
