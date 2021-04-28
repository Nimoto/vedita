<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(true);
?>
<div class="catalog-section">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<p><?= $arResult["NAV_STRING"]?></p>
<?endif?>
<table class="data-table" cellspacing="0" cellpadding="0" border="0" width="100%">
	<thead>
	<tr>
		<td><?= GetMessage("CT_BCS_NAME")?></td>
		<td><?= GetMessage("CT_BCS_CATEGORY")?></td>
		<td><?= GetMessage("CT_BCS_PRICE")?></td>
	</tr>
	</thead>

	<?foreach($arResult["ITEMS"] as $arElement) {?>
		<tr>
			<td><a href="<?= $arElement["DETAIL_PAGE_URL"]?>"><?= $arElement["NAME"]?></a></td>
			<td><?= ($arResult['SECTIONS'][$arElement["IBLOCK_SECTION_ID"]] ?? '')?></td>
			<td><?= $arElement['PRICE']['PRINT_BASE_PRICE']?></td>
		</tr>
	<?}?>
</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<p><?= $arResult["NAV_STRING"]?></p>
<?endif?>
</div>
