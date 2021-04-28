<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Задание 3");
?>
<?$APPLICATION->IncludeComponent(
    "test:catalog.section",
    ".default",
    Array(
    	'IBLOCK_ID' => 2,
    	'PRICE_ID' => 1,
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");