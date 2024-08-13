<?php
use secupay\Sdk\ApiClient;

class secupaySdkHelper
{

    /**
     *
     * @param string $gatewayBasePath
     * @param string $userId
     * @param string $userKey
     * @return \secupay\Sdk\ApiClient
     */
    public static function getApiClient($gatewayBasePath, $userId, $userKey): ApiClient
    {
        $client = new ApiClient($userId, $userKey);
        $client->setBasePath($gatewayBasePath . '/api');
        $client->addDefaultHeader('x-shop-system', 'plentymarkets');
        return $client;
    }

    /**
     *
     * @param float $amount
     * @param number $currencyDecimalPlaces
     * @return float
     */
    public static function roundAmount($amount, $currencyDecimalPlaces = 2)
    {
        return round($amount, $currencyDecimalPlaces);
    }

    /**
     *
     * @param \secupay\Sdk\Model\LineItem[] $lineItems
     * @return float
     */
    public static function calculateLineItemTotalAmount(array $lineItems)
    {
        $total = 0;
        foreach ($lineItems as $lineItem) {
            $total += $lineItem->getAmountIncludingTax();
        }
        return $total;
    }

    /**
     * Returns the amount of the line item's reductions.
     *
     * @param \secupay\Sdk\Model\LineItem[] $lineItems
     * @param \secupay\Sdk\Model\LineItemReduction[] $reductions
     * @param int $currencyDecimalPlaces
     * @return float
     */
    public static function getReductionAmount(array $lineItems, array $reductions, $currencyDecimalPlaces = 2)
    {
        $lineItemMap = array();
        foreach ($lineItems as $lineItem) {
            $lineItemMap[$lineItem->getUniqueId()] = $lineItem;
        }

        $amount = 0;
        foreach ($reductions as $reduction) {
            $lineItem = $lineItemMap[$reduction->getLineItemUniqueId()];
            $unitPrice = $lineItem->getAmountIncludingTax() / $lineItem->getQuantity();
            $amount += $unitPrice * $reduction->getQuantityReduction();
            $amount += $reduction->getUnitPriceReduction() * ($lineItem->getQuantity() - $reduction->getQuantityReduction());
        }

        return self::roundAmount($amount, $currencyDecimalPlaces);
    }

    /**
     * Convert data to string|array.
     *
     * @param mixed $data
     *            the data to string|array
     * @return string|array
     */
    public static function convertData($data)
    {
        return \secupay\Sdk\ObjectSerializer::sanitizeForSerialization($data);
    }

    /**
     * Creates and returns a new entity filter.
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $operator
     * @return \secupay\Sdk\Model\EntityQueryFilter
     */
    public static function createEntityFilter($fieldName, $value, $operator = \secupay\Sdk\Model\CriteriaOperator::EQUALS)
    {
        $filter = new \secupay\Sdk\Model\EntityQueryFilter();
        $filter->setType(\secupay\Sdk\Model\EntityQueryFilterType::LEAF);
        $filter->setOperator($operator);
        $filter->setFieldName($fieldName);
        $filter->setValue($value);
        return $filter;
    }

    /**
     * Creates and returns a new entity order by.
     *
     * @param string $fieldName
     * @param mixed $sortOrder
     * @return \secupay\Sdk\Model\EntityQueryOrderBy
     */
    public static function createEntityOrderBy($fieldName, $sortOrder = \secupay\Sdk\Model\EntityQueryOrderByType::DESC)
    {
        $orderBy = new \secupay\Sdk\Model\EntityQueryOrderBy();
        $orderBy->setFieldName($fieldName);
        $orderBy->setSorting($sortOrder);
        return $orderBy;
    }

    /**
     * @param $id
     * @param $array
     * @param $key
     * @return string
     */
    public static function checkForDuplicatePrefix($id, $array, $key) {
      if(!in_array($id, $array)) {
        return '';
      }

      return '_duplicate_' . $key;
    }
}
