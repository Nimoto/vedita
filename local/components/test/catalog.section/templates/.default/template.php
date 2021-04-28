<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(true);
?>
<div class="catalog-section">


<table class="data-table backlight" cellspacing="0" cellpadding="0" border="0" width="100%">
	<thead>
	<tr>
		<td><?= GetMessage("CT_BCS_NAME")?></td>
		<td><?= GetMessage("CT_BCS_CATEGORY")?></td>
		<td><?= GetMessage("CT_BCS_PRICE")?></td>
	</tr>
	</thead>

	<?foreach($arResult["ITEMS"] as $arElement) {?>
		<tr>
			<td><a href="<?= $arElement["FIELDS"]["DETAIL_PAGE_URL"]?>"><?= $arElement["FIELDS"]["NAME"]?></a></td>
			<td><?= ($arResult["SECTIONS"][$arElement["FIELDS"]["IBLOCK_SECTION_ID"]] ?? "")?></td>
			<td><?= $arElement["FIELDS"]["PRICE"]?></td>
		</tr>
	<?}?>
</table>

<?
$APPLICATION->IncludeComponent(
   "bitrix:main.pagenavigation",
   "",
   array(
      "NAV_OBJECT" => $arResult["NAV"],
      "SEF_MODE" => "N",
   ),
   false
);?>
</div>
