<?php

namespace Test\Components;

use CIBlockElement;

if (!\defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class CatalogSection extends \CBitrixComponent
{
    const DEFAULT_PAGE_SIZE = 20;
    const DEFAULT_PAGE_PARAM = 'page';

    /** @var int */
    protected $iblockId;

    /** @var int */
    protected $priceId;

    /** @var int */
    protected $pageParam;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $sectionId;

    /**
     * @param $arParams
     * @return array|void
     */
    public function onPrepareComponentParams($arParams)
    {
        $this->iblockId = (int) $arParams['IBLOCK_ID'];
        if (!$this->iblockId) {
            throw new \RuntimeException('Не указан ID инфоблока');
        }
        $this->priceId = (int) $arParams['PRICE_ID'];
        if (!$this->priceId) {
            throw new \RuntimeException('Не указан тип цены');
        }
        $this->sectionId = (int) $arParams['SECTION_ID'];

        $this->pageSize = (int) $arParams['PAGE_SIZE'] ?: self::DEFAULT_PAGE_SIZE;
        $this->pageParam = (int) $arParams['PAGE_PARAM'] ?: self::DEFAULT_PAGE_PARAM;
        
        return $arParams;
    }

    /**
     * @return array|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        $arSelect = $this->getSelect();
        $arFilter = $this->getFilter();
        $nav = $this->getNav();
        $this->arResult['NAV'] = $nav;

        $productResult = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            [
                "nPageSize" => $nav->getLimit(),
                'iNumPage' => $nav->getCurrentPage(),
            ],
            $arSelect
        );

        while ($element = $productResult->GetNextElement()) {
            $elementFields = $element->GetFields();
            $elementProperties = $element->GetProperties();
            $this->arResult['ITEMS'][$elementFields['ID']] = [
                'FIELDS' => $elementFields,
                'PROPERTIES' => $elementProperties,
                'OFFERS' => [],
            ];
            $productIds[] = $elementFields['ID'];
        }

        $offerResult = $this->getOffers($productIds);

        $offers = [];
        if (!empty($offerResult)) {
            $offerIds = [];
            foreach ($offerResult as $productId => $offers) {
                $this->arResult['ITEMS'][$productId]['OFFERS'] = $offers;
                foreach ($offers as $fields) {
                    $offerIds[] = $fields['ID'];
                    $offerIblockId = $fields['IBLOCK_ID'];
                }
            }
            $offers = $this->getOfferPrices($offerIblockId, $offerIds);
        }

        $sectionIds = [];
        foreach ($this->arResult['ITEMS'] as &$item) {
            $sectionIds[] = $item['FIELDS']['IBLOCK_SECTION_ID'];
            $item['FIELDS']['PRICE'] = $this->getPrice($item, $offers);
        }
        unset($item);

        $sections = $this->getSections($sectionIds);

        $this->arResult['SECTIONS'] = [];
        foreach ($sections as $section) {
            $this->arResult['SECTIONS'][$section['ID']] = $section['NAME'];
        }


        $this->includeComponentTemplate();

        return $this->arResult;
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        $arFilter = [
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
        ];

        if ($this->sectionId) {
            $arFilter['SECTION_ID'] = $this->sectionId;
        }

        return $arFilter;
    }

    /**
     * @return array
     */
    protected function getSelect(): array
    {
        return [
            'ID',
            'NAME',
            'IBLOCK_SECTION_ID',
            'DETAIL_PAGE_URL',
            'PRICE_' . $this->priceId,
        ];
    }

    /**
     * @return \Bitrix\Main\UI\PageNavigation
     */
    protected function getNav(): \Bitrix\Main\UI\PageNavigation
    {
        $nav = new \Bitrix\Main\UI\PageNavigation($this->pageParam);
        $nav->allowAllRecords(true)
            ->setPageSize($this->pageSize)
            ->initFromUri();

        $allCount = CIBlockElement::GetList(
            [],
            $this->getFilter(),
            [],
            false,
            []
        );
        $nav->setRecordCount($allCount);

        return $nav;
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function getOffers(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return \CCatalogSku::getOffersList(
            $productIds,
            $this->iblockId,
            ['ACTIVE' => 'Y']
        );
    }

    /**
     * @param int $offerIblockId
     * @param array $offerIds
     * @return array
     */
    protected function getOfferPrices(int $offerIblockId, array $offerIds): array
    {
        $offers = [];
        if (!empty($offerIds)) {
            $offerResult = CIBlockElement::GetList(
                ['PRICE_' . $this->priceId => 'ASC'],
                [
                    'IBLOCK_ID' => $offerIblockId,
                    'ID' => $offerIds
                ],
                false,
                [],
                [
                    'ID',
                    'IBLOCK_ID',
                    'PRICE_' . $this->priceId
                ]
            );

            while ($elementFields = $offerResult->GetNext()) {
                $offers[$elementFields['ID']] = $elementFields;
            }
        }

        return $offers;
    }

    /**
     * @param array $item
     * @param array $offers
     * @return mixed
     */
    protected function getPrice(array $item, array $offers)
    {
        if (empty($item)) {
            throw new \RuntimeException('Невалидные данные товара');
        }

        $haveOffers = !empty($item['OFFERS']);
        if ($haveOffers) {
            $actualItem = reset($item['OFFERS']);
            if (!isset($offers[$actualItem['ID']])) {
                throw new \RuntimeException('Не найдена цена');
            }
            $price = $offers[$actualItem['ID']]['PRICE_' . $this->priceId];
        } else {
            $price = $item['FIELDS']['PRICE_' . $this->priceId];
        }

        return $price;
    }

    /**
     * @param array $sectionIds
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getSections(array $sectionIds): array
    {
        if (empty($sectionIds)) {
            return [];
        }

        $sections = [];
        if ($sectionIds) {
            $sections = \Bitrix\Iblock\SectionTable::getList(
                [
                    'filter' => ['IBLOCK_ID' => $this->arParams['IBLOCK_ID'], '@ID' => $sectionIds],
                    'select' => ['ID', 'NAME'],
                ]
            )->fetchAll();
        }

        return $sections;
    }
}