<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$sectionIds = [];
foreach ($arResult['ITEMS'] as &$item) {
    $haveOffers = !empty($item['OFFERS']);
    if ($haveOffers) {
        $actualItem = isset($item['OFFERS'][$item['OFFERS_SELECTED']])
            ? $item['OFFERS'][$item['OFFERS_SELECTED']]
            : reset($item['OFFERS']);
        $price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
    } else {
        $price = $item['ITEM_START_PRICE'];
    }

    $sectionIds[] = $item['IBLOCK_SECTION_ID'];

    $item['PRICE'] = $price;
}
unset($item);

$sections = \Bitrix\Iblock\SectionTable::getList(
    [
        'filter' => ['IBLOCK_ID' => $arParams['IBLOCK_ID'], '@ID' => $sectionIds],
        'select' => ['ID', 'NAME'],
    ]
)->fetchAll();

$arResult['SECTIONS'] = [];
foreach ($sections as $section) {
	$arResult['SECTIONS'][$section['ID']] = $section['NAME'];
}